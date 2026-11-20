<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VariantiRequest;
use App\Models\Variant;
use App\Services\Stima\InterventiPrevisti;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/varianti?modello_id=…&anno=… — le versioni di un modello; con l'anno, solo quelle prodotte in quell'anno.
 * Per ogni versione dice quali interventi non proporre (es. frizione con il cambio automatico).
 */
class VariantiController extends Controller
{
    public function __invoke(VariantiRequest $request, InterventiPrevisti $previsti): JsonResponse
    {
        $anno = $request->validated('anno');

        $varianti = Variant::where('modello_id', $request->validated('modello_id'))
            ->when($anno !== null, fn ($q) => $q
                ->where('anno_produzione_inizio', '<=', $anno)
                ->where(fn ($q) => $q->whereNull('anno_produzione_fine')->orWhere('anno_produzione_fine', '>=', $anno)))
            ->orderBy('etichetta')
            ->get()
            ->map(fn (Variant $v) => self::formato($v, $previsti));

        return response()->json(['varianti' => $varianti]);
    }

    /** Una versione come la vede il sito React; usata anche da /api/opzioni. */
    public static function formato(Variant $v, InterventiPrevisti $previsti): array
    {
        return [
            'id' => $v->id,
            'etichetta' => $v->etichetta,
            'alimentazione' => $v->alimentazione,
            'cambio' => $v->cambio,
            'anno_produzione_inizio' => $v->anno_produzione_inizio,
            'anno_produzione_fine' => $v->anno_produzione_fine,
            'interventi_non_previsti' => $previsti->nonPrevisti($v),
        ];
    }
}
