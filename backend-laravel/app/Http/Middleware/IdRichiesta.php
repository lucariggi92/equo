<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dà a ogni richiesta un codice unico, che torna nella risposta (X-Request-Id) e compare in ogni riga di log:
 * dopo un problema permette di ricostruire cosa è successo. Il codice lo genera sempre il server,
 * non si accetta quello mandato da fuori.
 */
class IdRichiesta
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = (string) Str::uuid();
        Context::add('id_richiesta', $id);

        $response = $next($request);
        $response->headers->set('X-Request-Id', $id);

        return $response;
    }
}
