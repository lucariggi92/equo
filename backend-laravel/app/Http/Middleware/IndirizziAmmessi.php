<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Facoltativo (decisione D8): se EQUO_GESTIONE_IP contiene degli indirizzi (o reti, es. 192.168.1.0/24),
 * l'area riservata si apre solo da lì; dagli altri risponde "non trovato", senza dire che l'area esiste.
 * Vuoto = nessun limite (oggi).
 */
class IndirizziAmmessi
{
    public function handle(Request $request, Closure $next): Response
    {
        $ammessi = config('equo.gestione_indirizzi_ammessi');
        if ($ammessi !== [] && ! IpUtils::checkIp((string) $request->ip(), $ammessi)) {
            abort(404);
        }

        return $next($request);
    }
}
