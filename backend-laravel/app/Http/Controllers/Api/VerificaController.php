<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerificaRequest;
use App\Models\QuoteRequest;
use App\Models\Setting;
use App\Services\Stima\CalcoloStima;
use App\Services\Stima\Decimale;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/verifica — calcola stima e verdetto e salva la richiesta in forma anonima (quote_requests).
 * Se l'intervento non è in elenco (altro_intervento), salva solo la richiesta: serve a capire cosa aggiungere.
 */
class VerificaController extends Controller
{
    public function __invoke(VerificaRequest $request, CalcoloStima $calcolo): JsonResponse
    {
        $dati = $request->validated();
        $importo = Decimale::arrotonda((string) $dati['importo_preventivo'], 2);
        $ivaInclusa = (bool) $dati['iva_inclusa'];
        $richiesta = [
            'variante_id' => $dati['variante_id'],
            'anno_immatricolazione' => $dati['anno_immatricolazione'] ?? null,
            'intervento_id' => $dati['intervento_id'] ?? null,
            // se c'è l'intervento scelto dal menu, il testo libero non si salva
            'altro_intervento' => isset($dati['intervento_id']) ? null : ($dati['altro_intervento'] ?? null),
            'qualita' => $dati['qualita'],
            'regione_id' => $dati['regione_id'],
            'tipo_officina' => $dati['tipo_officina'],
            'importo_preventivo' => $importo,
            'iva_inclusa' => $ivaInclusa,
        ];

        if ($richiesta['intervento_id'] === null) {
            QuoteRequest::create($richiesta + ['esito' => 'non_calcolabile']);

            return response()->json([
                'esito' => 'non_calcolabile',
                'messaggio' => 'Questo intervento non è ancora tra quelli che sappiamo stimare. La richiesta è registrata, '
                    .'in forma anonima, e ci aiuta a decidere cosa aggiungere.',
            ]);
        }

        // il verdetto confronta importi IVA inclusa: un preventivo senza IVA si porta al lordo
        $preventivoLordo = $ivaInclusa ? $importo : Decimale::arrotonda(
            Decimale::per($importo, Decimale::somma('1', Decimale::diviso(Setting::findOrFail('iva_pct')->valore, '100', 2))), 2);

        $stima = $calcolo->calcola(
            (int) $dati['variante_id'], (int) $dati['intervento_id'], $dati['qualita'],
            (int) $dati['regione_id'], $dati['tipo_officina'], $preventivoLordo,
        );

        QuoteRequest::create($richiesta + [
            'stima_calcolata' => $stima->stimaIvaInclusa,
            'scostamento_pct' => $stima->scostamentoPct,
            'esito' => $stima->esito,
        ]);

        return response()->json($stima->toArray() + ['preventivo_iva_inclusa' => $preventivoLordo]);
    }
}
