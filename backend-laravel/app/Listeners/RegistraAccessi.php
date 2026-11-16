<?php

namespace App\Listeners;

use App\Models\AdminAuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * Scrive nel registro delle azioni ogni accesso all'area riservata: riuscito, fallito, bloccato per troppi tentativi,
 * uscita (decisione D8). Il registro si può solo aggiungere: nessuno può cancellare le tracce.
 */
class RegistraAccessi
{
    public function handleLogin(Login $evento): void
    {
        $this->scrivi('accesso', 'riuscita', $evento->user?->getAuthIdentifier());
    }

    public function handleFailed(Failed $evento): void
    {
        $email = $evento->credentials['email'] ?? null;
        $this->scrivi('accesso_fallito', 'fallita', $evento->user?->getAuthIdentifier(),
            ['email_inserita' => $email === null ? null : Str::limit((string) $email, 120, '')]);
    }

    public function handleLockout(Lockout $evento): void
    {
        $this->scrivi('accesso_bloccato', 'fallita', null, ['motivo' => 'troppi tentativi']);
    }

    public function handleLogout(Logout $evento): void
    {
        $this->scrivi('uscita', 'riuscita', $evento->user?->getAuthIdentifier());
    }

    /** @param array<string, mixed> $dettagli */
    private function scrivi(string $azione, string $esito, mixed $utente, array $dettagli = []): void
    {
        $richiesta = request();
        AdminAuditLog::create([
            'user_id' => $utente,
            'azione' => $azione,
            'oggetto' => 'area riservata',
            'dettagli' => $dettagli + ['id_richiesta' => Context::get('id_richiesta')],
            'esito' => $esito,
            'indirizzo_ip' => $richiesta?->ip(),
            'user_agent' => Str::limit((string) $richiesta?->userAgent(), 255, ''),
        ]);
    }
}
