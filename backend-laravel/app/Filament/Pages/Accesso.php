<?php

namespace App\Filament\Pages;

use App\Models\AdminAuditLog;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Login;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * Pagina di accesso dell'area riservata: quella di Filament (massimo 5 tentativi al minuto),
 * più la scrittura nel registro quando i tentativi vengono bloccati.
 * Accessi riusciti e falliti li registra App\Listeners\RegistraAccessi.
 */
class Accesso extends Login
{
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        AdminAuditLog::create([
            'azione' => 'accesso_bloccato',
            'oggetto' => 'area riservata',
            'dettagli' => ['motivo' => 'troppi tentativi', 'secondi_di_attesa' => $exception->secondsUntilAvailable,
                'id_richiesta' => Context::get('id_richiesta')],
            'esito' => 'fallita',
            'indirizzo_ip' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
        ]);

        return parent::getRateLimitedNotification($exception);
    }
}
