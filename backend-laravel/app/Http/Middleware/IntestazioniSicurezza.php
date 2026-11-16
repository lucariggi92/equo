<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intestazioni di sicurezza su ogni risposta (decisione D8).
 *  - X-Content-Type-Options: il browser non "indovina" il tipo di file (niente script travestiti);
 *  - X-Frame-Options / frame-ancestors: nessun sito può mostrare le nostre pagine dentro le sue (siti truffa);
 *  - Referrer-Policy: non diciamo agli altri siti da quale nostra pagina arriva l'utente;
 *  - Permissions-Policy: niente fotocamera, microfono, posizione, pagamenti;
 *  - Content-Security-Policy: sull'API nessuna risorsa può essere caricata o eseguita.
 * Toglie le intestazioni che rivelano il software usato (X-Powered-By).
 * La CSP dell'area riservata si aggiunge con il task L7 (servono le regole del pannello).
 */
class IntestazioniSicurezza
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $h = $response->headers;
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'DENY');
        $h->set('Referrer-Policy', 'no-referrer');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');
        if ($request->is('api/*')) {
            $h->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'");
        } elseif (! $h->has('Content-Security-Policy')) {
            // area riservata (L7): solo risorse del nostro server; il pannello usa script e stili in linea
            // e la valutazione di espressioni (Alpine.js), quindi servono 'unsafe-inline' e 'unsafe-eval'
            $h->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
                ."style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; "
                ."frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
            $h->set('Cache-Control', 'no-store, private');
        }
        $h->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }
}
