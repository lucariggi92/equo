<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tutto il traffico passa cifrato (decisione D8).
 * Con equo.forza_https attivo: una lettura (GET/HEAD) non cifrata viene rimandata su HTTPS,
 * ogni altra richiesta non cifrata viene rifiutata (i dati non devono viaggiare in chiaro nemmeno una volta).
 * Sulle risposte cifrate aggiunge HSTS: il browser userà solo HTTPS per un anno.
 */
class ForzaHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('equo.forza_https') && ! $request->isSecure()) {
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return redirect()->secure($request->getRequestUri(), 301);
            }

            return response()->json(['message' => 'Connessione non cifrata: usa HTTPS.'], 403);
        }

        $response = $next($request);
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
