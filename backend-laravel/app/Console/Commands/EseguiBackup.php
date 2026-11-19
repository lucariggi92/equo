<?php

namespace App\Console\Commands;

use App\Services\Manutenzione\Backup;
use App\Services\Registro;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Backup del database dal server (task L8). Lo lancia anche la pianificazione notturna (routes/console.php).
 * Ogni backup, riuscito o no, finisce nel registro delle azioni.
 */
#[Signature('equo:backup')]
#[Description('Backup completo del database (tabelle, dati, procedure) con inventario per verificare il ripristino')]
class EseguiBackup extends Command
{
    public function handle(Backup $backup): int
    {
        try {
            $r = $backup->esegui();
        } catch (Throwable $e) {
            Registro::scrivi('backup', null, ['errore' => $e->getMessage(), 'da' => 'comando'], 'fallita');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        Registro::scrivi('backup', $r['file'], $r + ['da' => 'comando']);
        $this->info("Backup fatto: {$r['file']} ({$r['mb']} MB, {$r['tabelle']} tabelle, {$r['procedure']} procedure)");

        return self::SUCCESS;
    }
}
