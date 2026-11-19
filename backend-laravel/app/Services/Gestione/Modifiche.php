<?php

namespace App\Services\Gestione;

use App\Services\Registro;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Le sole modifiche che l'area riservata può fare a mano (task L8): impostazioni, modelli pronti per il form,
 * interventi visibili. Nessun prezzo, codice, ora o tariffa: quelli entrano solo dall'import.
 * Ogni modifica passa dall'utente MySQL equo_import, in una transazione, e finisce nel registro con il valore precedente.
 */
final class Modifiche
{
    /** Impostazioni modificabili, con i limiti ammessi. */
    public const IMPOSTAZIONI = [
        'iva_pct' => ['min' => 0, 'max' => 50, 'decimali' => 2, 'nome' => 'Aliquota IVA (%)'],
        'ore_minime' => ['min' => 0, 'max' => 8, 'decimali' => 2, 'nome' => 'Ore minime fatturate'],
        'soglia_sopra_media' => ['min' => 0.01, 'max' => 1, 'decimali' => 2, 'nome' => 'Soglia "sopra la media" (0,10 = +10%)'],
        'soglia_anomalo' => ['min' => 0.02, 'max' => 2, 'decimali' => 2, 'nome' => 'Soglia "anomalo" (0,25 = +25%)'],
        'soglia_molto_sotto' => ['min' => -1, 'max' => -0.01, 'decimali' => 2, 'nome' => 'Soglia "molto sotto" (-0,25 = -25%)'],
        'scarto_campione' => ['min' => 0.05, 'max' => 1, 'decimali' => 2, 'nome' => 'Scarto massimo di un negozio dalla mediana'],
        'taratura_min_fonti' => ['min' => 1, 'max' => 20, 'decimali' => 0, 'nome' => 'Taratura: fonti minime'],
        'taratura_scarto' => ['min' => 0, 'max' => 1, 'decimali' => 2, 'nome' => 'Taratura: scarto massimo'],
        'taratura_dispersione' => ['min' => 0, 'max' => 2, 'decimali' => 2, 'nome' => 'Taratura: dispersione massima'],
        'taratura_anni_max' => ['min' => 1, 'max' => 20, 'decimali' => 0, 'nome' => 'Taratura: anni massimi'],
    ];

    public function impostazione(string $chiave, string $valore): void
    {
        $regola = self::IMPOSTAZIONI[$chiave] ?? throw ValidationException::withMessages(['valore' => 'Questa impostazione non si modifica dal pannello.']);
        $numero = str_replace(',', '.', trim($valore));
        if (! is_numeric($numero) || $numero < $regola['min'] || $numero > $regola['max']) {
            throw ValidationException::withMessages(['valore' => "Valore ammesso: da {$regola['min']} a {$regola['max']}."]);
        }
        $nuovo = number_format((float) $numero, $regola['decimali'], '.', '');

        $db = DB::connection('import');
        $db->transaction(function () use ($db, $chiave, $nuovo) {
            $tutte = $db->table('settings')->lockForUpdate()->pluck('valore', 'chiave');
            $dopo = [...$tutte->all(), $chiave => $nuovo];
            if ((float) $dopo['soglia_sopra_media'] >= (float) $dopo['soglia_anomalo']) {
                throw ValidationException::withMessages(['valore' => 'La soglia "sopra la media" deve restare più bassa della soglia "anomalo".']);
            }
            $prima = $tutte[$chiave] ?? null;
            if ($prima === $nuovo) {
                return;
            }
            $db->table('settings')->where('chiave', $chiave)->update(['valore' => $nuovo]);
            Registro::scrivi('impostazione_modificata', $chiave, ['prima' => $prima, 'dopo' => $nuovo]);
        });
    }

    public function modelloPronto(int $modelloId, bool $pronto): void
    {
        $this->interruttore('car_models', 'info_completed', $modelloId, $pronto, 'modello_pronto_per_il_form');
    }

    public function interventoVisibile(int $interventoId, bool $visibile): void
    {
        $this->interruttore('interventions', 'visibile_nel_form', $interventoId, $visibile, 'intervento_visibile_nel_form');
    }

    private function interruttore(string $tabella, string $colonna, int $id, bool $valore, string $azione): void
    {
        $db = DB::connection('import');
        $db->transaction(function () use ($db, $tabella, $colonna, $id, $valore, $azione) {
            $riga = $db->table($tabella)->where('id', $id)->lockForUpdate()->first(['nome', $colonna]);
            if ($riga === null) {
                throw ValidationException::withMessages(['id' => 'Elemento non trovato.']);
            }
            if ((bool) $riga->{$colonna} === $valore) {
                return;
            }
            $db->table($tabella)->where('id', $id)->update([$colonna => $valore]);
            Registro::scrivi($azione, $riga->nome, ['prima' => (bool) $riga->{$colonna}, 'dopo' => $valore]);
        });
    }
}
