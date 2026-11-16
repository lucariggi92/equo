<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // niente rotta "/up" di Laravel: lo stato del servizio si legge solo da /api/salute (decisione D8)
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Controlli su tutte le richieste (decisione D8, task L6). Ordine dall'esterno verso l'interno:
        // codice della richiesta, intestazioni di sicurezza, HTTPS; poi quelli di Laravel (proxy, CORS, ...);
        // infine i controlli dell'API (metodi, dimensione, formato), dopo il CORS così il browser può fare il suo controllo.
        $middleware->prepend([
            \App\Http\Middleware\IdRichiesta::class,
            \App\Http\Middleware\IntestazioniSicurezza::class,
            \App\Http\Middleware\ForzaHttps::class,
        ]);
        $middleware->append(\App\Http\Middleware\ControlloRichiesteApi::class);

        // ci si fida solo del proxy indicato in TRUSTED_PROXIES (es. quello del server); di default di nessuno,
        // così nessuno può fingere un altro indirizzo per aggirare i limiti di traffico
        $middleware->trustProxies(at: env('TRUSTED_PROXIES') ?: []);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
