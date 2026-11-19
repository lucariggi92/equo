<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * Scrittura nel registro delle azioni dell'area riservata (decisione D8): chi, quando, cosa, con quale esito,
 * da quale indirizzo e browser. Il registro si può solo aggiungere.
 */
final class Registro
{
    /** @param array<string, mixed> $dettagli */
    public static function scrivi(string $azione, ?string $oggetto = null, array $dettagli = [], string $esito = 'riuscita', mixed $utente = null): void
    {
        $richiesta = app()->runningInConsole() ? null : request();

        AdminAuditLog::create([
            'user_id' => $utente ?? auth()->id(),
            'azione' => $azione,
            'oggetto' => $oggetto === null ? null : Str::limit($oggetto, 120, ''),
            'dettagli' => $dettagli + array_filter(['id_richiesta' => Context::get('id_richiesta')]),
            'esito' => $esito,
            'indirizzo_ip' => $richiesta?->ip(),
            'user_agent' => $richiesta === null ? 'comando sul server' : Str::limit((string) $richiesta->userAgent(), 255, ''),
        ]);
    }
}
