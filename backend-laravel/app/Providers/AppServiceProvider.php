<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Limiti di traffico dell'API per il sito React (decisione D8), per indirizzo del visitatore.
        // L'indirizzo serve solo al contatore, per pochi minuti, e non viene salvato nel database.
        RateLimiter::for('letture', fn (Request $request) => Limit::perMinute(config('equo.limiti.letture_al_minuto'))
            ->by('letture|'.$request->ip()));

        RateLimiter::for('verifica', fn (Request $request) => [
            Limit::perMinute(config('equo.limiti.verifiche_al_minuto'))->by('verifica-minuto|'.$request->ip()),
            Limit::perDay(config('equo.limiti.verifiche_al_giorno'))->by('verifica-giorno|'.$request->ip()),
        ]);
    }
}
