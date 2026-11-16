<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlli sulle richieste all'API, prima che arrivino alle rotte (decisione D8):
 *  - metodi: solo GET e HEAD per le letture, POST solo su /api/verifica, OPTIONS per il controllo CORS del browser;
 *    PUT, PATCH e DELETE sempre rifiutati, anche se travestiti (X-HTTP-Method-Override o campo _method);
 *  - dimensione: oltre il limite (16 KB) la richiesta viene rifiutata;
 *  - formato: le POST devono essere JSON.
 * Agisce solo sugli indirizzi /api/...; le risposte sono sempre JSON.
 */
class ControlloRichiesteApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $metodo = $request->getRealMethod();
        if ($request->headers->has('X-HTTP-Method-Override') || $request->query->has('_method') || $request->request->has('_method')
            || ($request->isJson() && is_array($request->json()->all()) && array_key_exists('_method', $request->json()->all()))) {
            return $this->rifiuta('Richiesta non valida.', 400);
        }
        $permessi = $request->is('api/verifica') ? ['POST', 'OPTIONS'] : ['GET', 'HEAD', 'OPTIONS'];
        if (! in_array($metodo, $permessi, true)) {
            return $this->rifiuta('Metodo non permesso.', 405)->header('Allow', implode(', ', $permessi));
        }

        $dimensione = (int) $request->headers->get('Content-Length', strlen((string) $request->getContent()));
        if ($dimensione > config('equo.dimensione_massima_richiesta')) {
            return $this->rifiuta('Richiesta troppo grande.', 413);
        }

        if ($metodo === 'POST' && ! $request->isJson()) {
            return $this->rifiuta('La richiesta deve essere in formato JSON.', 415);
        }

        return $next($request);
    }

    private function rifiuta(string $messaggio, int $stato): Response
    {
        return response()->json(['message' => $messaggio], $stato);
    }
}
