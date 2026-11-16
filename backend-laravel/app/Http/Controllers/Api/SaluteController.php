<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * GET /api/salute — dice solo se il servizio e il database rispondono. Nessuna versione né dettaglio interno.
 */
class SaluteController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
        } catch (Throwable $e) {
            report($e);

            return response()->json(['stato' => 'errore'], 503);
        }

        return response()->json(['stato' => 'ok']);
    }
}
