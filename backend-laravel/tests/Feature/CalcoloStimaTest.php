<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Region;
use App\Models\Variant;
use App\Services\Stima\CalcoloStima;
use App\Services\Stima\Stima;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * L4: il calcolo PHP dà gli stessi risultati della procedura MySQL calcola_stima.
 * I valori attesi sono quelli restituiti dalla procedura il 16/11/2026 sugli stessi dati
 * (il confronto completo, 14.202 casi, si rifà con: php artisan equo:confronta-stime).
 */
class CalcoloStimaTest extends TestCase
{
    private function stima(string $modello, string $intervento, string $qualita, string $officina, string $regione, ?string $preventivo = null): Stima
    {
        return app(CalcoloStima::class)->calcola(
            Variant::whereHas('modello', fn ($q) => $q->where('nome', $modello))->value('id'),
            Intervention::where('nome', $intervento)->value('id'),
            $qualita,
            Region::where('nome', $regione)->value('id'),
            $officina,
            $preventivo,
        );
    }

    public function test_tagliando_panda_riepilogo_completo(): void
    {
        $s = $this->stima('Panda (319)', 'Tagliando completo', 'equivalente', 'generica', 'Lombardia', '230');

        $this->assertSame('98.33', $s->ricambiNetto);
        $this->assertSame('24.58', $s->ricaricoNetto);
        $this->assertSame('76.14', $s->manodoperaNetto);
        $this->assertSame('199.05', $s->imponibile);
        $this->assertSame('43.79', $s->iva);
        $this->assertSame('242.84', $s->stimaIvaInclusa);
        $this->assertSame('218.43', $s->stimaDa);
        $this->assertSame('259.79', $s->stimaA);
        $this->assertSame([5, 5], [$s->ricambiRichiesti, $s->ricambiConPrezzo]);
        $this->assertSame(['35', '26', '39'], [$s->ricambiVerificatiPct, $s->ricambiIndicativiPct, $s->ricambiStimatiPct]);
        $this->assertTrue($s->completo);
        $this->assertSame([], $s->mancanti);
        $this->assertSame(['-5.3', 'equo', null], [$s->scostamentoPct, $s->esito, $s->avviso]);
    }

    public function test_dettaglio_tagliando_panda(): void
    {
        $righe = $this->stima('Panda (319)', 'Tagliando completo', 'equivalente', 'generica', 'Lombardia')->toArray()['dettaglio'];

        $this->assertSame(['Candela', 'Filtro abitacolo', 'Filtro aria', 'Filtro olio', 'Olio motore', 'Manodopera (tempario, tariffa assunzione)'],
            array_column($righe, 'voce'));
        $filtro = $righe[3];
        $this->assertSame(['MANN-FILTER', 'W 610/3', '8.06', 'rilevato', '25', '10.08', '5.51', '14.64'],
            [$filtro['produttore'], $filtro['codice'], $filtro['prezzo_unitario_netto'], $filtro['metodo'],
                $filtro['ricarico_pct'], $filtro['totale_netto'], $filtro['da'], $filtro['a']]);
        $manodopera = $righe[5];
        $this->assertSame(['1.41', '54.00', '76.14'], [$manodopera['quantita'], $manodopera['prezzo_unitario_netto'], $manodopera['totale_netto']]);
    }

    /** @return array<string, array{0: string, 1: string, 2: string, 3: ?string}> */
    public static function verdetti(): array
    {
        return [
            'sopra la media' => ['300', '23.5', 'sopra_media', null],
            'anomalo' => ['350', '44.1', 'anomalo', null],
            'molto sotto, con avviso' => ['150', '-38.2', 'equo', 'molto sotto la stima: verifica cosa è incluso'],
        ];
    }

    #[DataProvider('verdetti')]
    public function test_verdetti_sul_tagliando_panda(string $preventivo, string $scostamento, string $esito, ?string $avviso): void
    {
        $s = $this->stima('Panda (319)', 'Tagliando completo', 'equivalente', 'generica', 'Lombardia', $preventivo);

        $this->assertSame([$scostamento, $esito, $avviso], [$s->scostamentoPct, $s->esito, $s->avviso]);
    }

    public function test_tagliando_senza_olio_non_e_completo(): void
    {
        $s = $this->stima('Clio IV (BH)', 'Tagliando completo', 'equivalente', 'generica', 'Lombardia', '200');

        $this->assertSame(['171.25', '148.27', '193.18'], [$s->stimaIvaInclusa, $s->stimaDa, $s->stimaA]);
        $this->assertFalse($s->completo);
        $this->assertSame(['Olio motore (quantità non nota)'], $s->mancanti);
        $this->assertSame(['16.8', 'dati_insufficienti'], [$s->scostamentoPct, $s->esito]);
    }

    public function test_qualita_originale_senza_prezzi(): void
    {
        $s = $this->stima('Panda (319)', 'Tagliando completo', 'originale', 'generica', 'Lombardia', '230');

        $this->assertSame('131.39', $s->stimaIvaInclusa);
        $this->assertFalse($s->completo);
        $this->assertEqualsCanonicalizing(['Filtro olio', 'Filtro aria', 'Filtro abitacolo', 'Candela'], $s->mancanti);
        $this->assertSame(['75.1', 'dati_insufficienti'], [$s->scostamentoPct, $s->esito]);
    }

    public function test_altra_regione_e_officina(): void
    {
        $s = $this->stima('Golf VII (5G1)', 'Freni anteriori', 'economico', 'concessionaria', 'Lazio');

        $this->assertSame(['195.56', '180.30', '202.72'], [$s->stimaIvaInclusa, $s->stimaDa, $s->stimaA]);
        $this->assertTrue($s->completo);
        $this->assertNull($s->esito);
    }

    public function test_intervento_che_non_esiste_su_quella_auto(): void
    {
        // la Yaris ibrida non ha frizione: nessuna riga, nessuna stima
        $s = $this->stima('Yaris III (XP13)', 'Frizione', 'equivalente', 'generica', 'Lombardia', '500');

        $this->assertSame([], $s->righe);
        $this->assertNull($s->stimaIvaInclusa);
        $this->assertFalse($s->completo);
        $this->assertSame('dati_insufficienti', $s->esito);
    }

    public function test_valori_non_ammessi_rifiutati(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stima('Panda (319)', 'Tagliando completo', 'usato', 'generica', 'Lombardia');
    }
}
