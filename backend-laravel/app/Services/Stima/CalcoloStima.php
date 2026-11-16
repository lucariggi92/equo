<?php

namespace App\Services\Stima;

use App\Models\Intervention;
use App\Models\InterventionComponent;
use App\Models\LaborHour;
use App\Models\LaborRate;
use App\Models\Markup;
use App\Models\PartQuality;
use App\Models\Setting;
use InvalidArgumentException;

/**
 * Calcolo della stima e del verdetto (task L4): stessa logica della procedura MySQL
 * calcola_stima / prepara_stima (database-mysql/queries/calcolo_stima.sql), provata nella fase 1.
 *
 * Regole:
 *  - importi netti di IVA; l'IVA (settings.iva_pct) si applica una volta sola, alla fine;
 *  - ricarico solo sui ricambi, riga per riga, da markups(tipo di officina, tipo di prezzo);
 *  - ore fatturate = la maggiore tra ore stimate e settings.ore_minime;
 *  - manca un prezzo, la tariffa, le ore, o l'olio in tagliando/cambio olio: completo = false,
 *    verdetto "dati_insufficienti";
 *  - verdetto: equo fino a +10%, sopra_media fino a +25%, oltre anomalo; sotto -25% resta equo con avviso.
 * Gli arrotondamenti sono quelli di MySQL (vedi Decimale).
 */
final class CalcoloStima
{
    public const QUALITA = ['originale', 'equivalente', 'economico'];

    public const OFFICINE = ['concessionaria', 'generica', 'catena'];

    /** Interventi che senza olio motore non sono completi. */
    public const INTERVENTI_CON_OLIO = ['Tagliando completo', 'Cambio olio e filtro'];

    public function calcola(int $varianteId, int $interventoId, string $qualita, int $regioneId, string $officina, ?string $preventivo = null): Stima
    {
        if (! in_array($qualita, self::QUALITA, true)) {
            throw new InvalidArgumentException("Qualità non valida: {$qualita}");
        }
        if (! in_array($officina, self::OFFICINE, true)) {
            throw new InvalidArgumentException("Tipo di officina non valido: {$officina}");
        }

        $impostazioni = Setting::whereIn('chiave', ['iva_pct', 'ore_minime', 'soglia_sopra_media', 'soglia_anomalo', 'soglia_molto_sotto'])
            ->pluck('valore', 'chiave');
        $iva = Decimale::diviso($impostazioni['iva_pct'], '100', 2);      // 22.00 / 100 = 0.220000
        $oreMinime = $impostazioni['ore_minime'];

        $righe = [...$this->righeRicambi($varianteId, $interventoId, $qualita, $officina)];
        $manodopera = $this->rigaManodopera($varianteId, $interventoId, $regioneId, $officina, $oreMinime);
        if ($manodopera !== null) {
            $righe[] = $manodopera;
        }
        // stesso ordine della procedura: prima i ricambi per nome, poi la manodopera
        usort($righe, fn (RigaStima $a, RigaStima $b) => [$b->tipo, $a->voce] <=> [$a->tipo, $b->voce]);

        $ricambi = array_values(array_filter($righe, fn (RigaStima $r) => $r->eRicambio()));
        $mancanti = array_values(array_map(fn (RigaStima $r) => $r->voce, array_filter($ricambi, fn (RigaStima $r) => $r->totaleNetto === null)));

        $nomeIntervento = Intervention::whereKey($interventoId)->value('nome');
        $olioMancante = in_array($nomeIntervento, self::INTERVENTI_CON_OLIO, true)
            && ! collect($ricambi)->contains(fn (RigaStima $r) => $r->voce === 'Olio motore');
        if ($olioMancante) {
            $mancanti[] = 'Olio motore (quantità non nota)';
        }

        $ciSonoRighe = $righe !== [];
        $somma = fn (callable $valore) => $ciSonoRighe ? Decimale::sommaSql(array_map($valore, $righe)) : null;
        $r2 = fn (?string $x) => $x === null ? null : Decimale::arrotonda($x, 2);

        $totale = $somma(fn (RigaStima $r) => $r->totaleNetto);
        $totaleMin = $somma(fn (RigaStima $r) => $r->totaleMin);
        $totaleMax = $somma(fn (RigaStima $r) => $r->totaleMax);
        $valoreRicambi = $somma(fn (RigaStima $r) => $r->eRicambio() ? $r->totaleNetto : '0');
        $quota = function (callable $condizione) use ($somma, $valoreRicambi): ?string {
            if ($valoreRicambi === null || Decimale::confronta($valoreRicambi, '0') === 0) {
                return null;
            }
            $parte = $somma(fn (RigaStima $r) => $r->eRicambio() && $condizione($r) ? $r->totaleNetto : '0');

            return $parte === null ? null : Decimale::arrotonda(Decimale::diviso(Decimale::per('100', $parte), $valoreRicambi, 4), 0);
        };

        $manodoperaPresente = $ciSonoRighe ? $manodopera !== null : null;
        $completo = count($ricambi) > 0
            && count($mancanti) === 0
            && $manodopera !== null
            && ! $olioMancante;
        $stima = $r2($totale === null ? null : Decimale::per($totale, Decimale::somma('1', $iva)));

        [$scostamento, $esito, $avviso] = $this->verdetto($preventivo, $stima, $completo, $impostazioni->all());

        return new Stima(
            righe: $righe,
            ricambiNetto: $r2($somma(fn (RigaStima $r) => $r->eRicambio() ? $r->ricambioNetto : '0')),
            ricaricoNetto: $r2($somma(fn (RigaStima $r) => $r->eRicambio() ? $r->ricaricoNetto : '0')),
            manodoperaNetto: $r2($somma(fn (RigaStima $r) => $r->eRicambio() ? '0' : $r->totaleNetto)),
            imponibile: $r2($totale),
            iva: $r2($totale === null ? null : Decimale::per($totale, $iva)),
            stimaIvaInclusa: $stima,
            stimaDa: $r2($totaleMin === null ? null : Decimale::per($totaleMin, Decimale::somma('1', $iva))),
            stimaA: $r2($totaleMax === null ? null : Decimale::per($totaleMax, Decimale::somma('1', $iva))),
            ricambiRichiesti: $ciSonoRighe ? count($ricambi) : null,
            ricambiConPrezzo: $ciSonoRighe ? count($ricambi) - count(array_filter($ricambi, fn (RigaStima $r) => $r->totaleNetto === null)) : null,
            ricambiVerificatiPct: $quota(fn (RigaStima $r) => $r->metodo === 'rilevato'),
            ricambiIndicativiPct: $quota(fn (RigaStima $r) => $r->metodo === 'indicativo'),
            ricambiStimatiPct: $quota(fn (RigaStima $r) => $r->daAvvalorare === true && $r->metodo !== 'indicativo'),
            manodoperaPresente: $manodoperaPresente,
            completo: $completo,
            mancanti: $mancanti,
            preventivo: $preventivo === null ? null : Decimale::arrotonda($preventivo, 2),
            scostamentoPct: $scostamento,
            esito: $esito,
            avviso: $avviso,
        );
    }

    /** @return iterable<RigaStima> */
    private function righeRicambi(int $varianteId, int $interventoId, string $qualita, string $officina): iterable
    {
        $composizione = InterventionComponent::with('componente')
            ->where('variante_id', $varianteId)
            ->where('intervento_id', $interventoId)
            ->get();

        foreach ($composizione as $voce) {
            $ricambio = PartQuality::where('variante_id', $varianteId)
                ->where('componente_id', $voce->componente_id)
                ->where('qualita', $qualita)
                ->first()?->ricambio;
            $prezzo = $ricambio?->prezzoCorrente;
            $ricarico = $prezzo === null ? null : Markup::correnti()
                ->where('tipo_officina', $officina)
                ->where('tipo_fonte', $prezzo->tipo_fonte)
                ->value('fattore');

            $q = $voce->quantita;
            $p = $prezzo?->prezzo_netto;
            $r4 = fn (string $x) => Decimale::arrotonda($x, 4);
            $conRicarico = $p !== null && $ricarico !== null;
            $uno = $conRicarico ? Decimale::somma('1', $ricarico) : null;

            yield new RigaStima(
                tipo: 'ricambio',
                voce: $voce->componente->descrizione,
                quantita: $q,
                produttore: $ricambio?->produttore,
                codice: $ricambio?->codice,
                generico: $ricambio?->generico,
                prezzoNetto: $p,
                tipoFonte: $prezzo?->tipo_fonte,
                metodo: $prezzo?->metodo,
                daAvvalorare: $prezzo?->da_avvalorare,
                ricarico: $ricarico,
                ricambioNetto: $p === null ? null : $r4(Decimale::per($q, $p)),
                ricaricoNetto: $conRicarico ? $r4(Decimale::per($q, $p, $ricarico)) : null,
                totaleNetto: $conRicarico ? $r4(Decimale::per($q, $p, $uno)) : null,
                totaleMin: $conRicarico ? $r4(Decimale::per($q, $prezzo->prezzo_min ?? $p, $uno)) : null,
                totaleMax: $conRicarico ? $r4(Decimale::per($q, $prezzo->prezzo_max ?? $p, $uno)) : null,
            );
        }
    }

    private function rigaManodopera(int $varianteId, int $interventoId, int $regioneId, string $officina, string $oreMinime): ?RigaStima
    {
        $ore = LaborHour::where('variante_id', $varianteId)->where('intervento_id', $interventoId)->first();
        $tariffa = LaborRate::correnti()->where('regione_id', $regioneId)->where('tipo_officina', $officina)->first();
        if ($ore === null || $tariffa === null) {
            return null;
        }

        $oreFatturate = Decimale::max($ore->ore, $oreMinime);
        $totale = Decimale::arrotonda(Decimale::per($oreFatturate, $tariffa->tariffa_oraria_netta), 4);

        return new RigaStima(
            tipo: 'manodopera',
            voce: "Manodopera ({$ore->origine}, tariffa {$tariffa->origine})",
            quantita: Decimale::arrotonda($oreFatturate, 2),
            prezzoNetto: $tariffa->tariffa_oraria_netta,
            metodo: $ore->origine === 'reale' && $tariffa->origine === 'reale' ? 'rilevato' : 'assunzione',
            totaleNetto: $totale,
            totaleMin: $totale,
            totaleMax: $totale,
        );
    }

    /**
     * @param  array<string, string>  $impostazioni
     * @return array{0: ?string, 1: ?string, 2: ?string} scostamento %, esito, avviso
     */
    private function verdetto(?string $preventivo, ?string $stima, bool $completo, array $impostazioni): array
    {
        if ($preventivo === null) {
            return [null, null, null];
        }
        $preventivo = Decimale::arrotonda($preventivo, 2);
        if ($stima === null || Decimale::confronta($stima, '0') === 0) {
            return [null, $completo ? null : 'dati_insufficienti', null];
        }

        $rapporto = Decimale::diviso(Decimale::meno($preventivo, $stima), $stima, 2);    // (preventivo - stima) / stima
        $scostamento = Decimale::arrotonda(Decimale::per($rapporto, '100'), 1);

        $esito = match (true) {
            ! $completo => 'dati_insufficienti',
            Decimale::confronta($rapporto, $impostazioni['soglia_sopra_media']) <= 0 => 'equo',
            Decimale::confronta($rapporto, $impostazioni['soglia_anomalo']) <= 0 => 'sopra_media',
            default => 'anomalo',
        };
        $avviso = $completo && Decimale::confronta($rapporto, $impostazioni['soglia_molto_sotto']) < 0
            ? 'molto sotto la stima: verifica cosa è incluso'
            : null;

        return [$scostamento, $esito, $avviso];
    }
}
