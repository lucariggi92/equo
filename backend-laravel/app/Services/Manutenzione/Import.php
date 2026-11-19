<?php

namespace App\Services\Manutenzione;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Import del file Excel dei dati dall'area riservata (task L8).
 * Usa lo stesso script provato nella fase 1 (database-mysql/import/importa_dati.py), con l'utente MySQL equo_import:
 *   prova()  -> controlla tutto e annulla (niente salvato), restituisce il report;
 *   carica() -> backup automatico, poi import vero in un'unica transazione (tutto o niente).
 * Un solo import alla volta. Il file deve stare nella cartella degli import del server ed essere un .xlsx.
 */
final class Import
{
    public function __construct(private readonly Backup $backup) {}

    public static function cartella(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'import');
    }

    /** @return array{ok: bool, report: string} */
    public function prova(string $file): array
    {
        return $this->conBlocco(fn () => $this->esegui($file, true));
    }

    /** @return array{ok: bool, report: string, backup: string} */
    public function carica(string $file): array
    {
        return $this->conBlocco(function () use ($file) {
            $this->controllaFile($file);
            $backup = $this->backup->esegui();

            return $this->esegui($file, false) + ['backup' => $backup['file']];
        });
    }

    private function conBlocco(callable $lavoro): array
    {
        try {
            return Cache::lock('equo-import', 1800)->block(1, $lavoro);
        } catch (LockTimeoutException) {
            throw new RuntimeException('C\'è già un import in corso: riprova tra qualche minuto.');
        }
    }

    /** @return array{ok: bool, report: string} */
    private function esegui(string $file, bool $prova): array
    {
        $this->controllaFile($file);
        $c = config('database.connections.import');
        $cnf = storage_path('app/private/mysql-'.Str::uuid().'.cnf');
        File::put($cnf, "[client]\nuser={$c['username']}\npassword=\"{$c['password']}\"\nhost={$c['host']}\nport={$c['port']}\n");
        @chmod($cnf, 0600);

        $comando = [
            config('equo.import.python'), config('equo.import.script'), $file,
            '--cnf='.$cnf, '--database='.$c['database'], '--mysql='.config('equo.import.mysql'),
        ];
        if ($prova) {
            $comando[] = '--prova';
        }
        try {
            $esito = Process::timeout(900)
                ->path(dirname(config('equo.import.script'), 2))
                ->env(['PYTHONIOENCODING' => 'utf-8'])
                ->run($comando);
        } finally {
            File::delete($cnf);
        }

        $report = trim($esito->output());
        if ($report === '') {
            $report = 'Lo script non ha prodotto un report. Errore: '.Str::limit(trim($esito->errorOutput()), 1000);
        }

        return ['ok' => $esito->successful() && str_contains($report, 'Esito database: OK'), 'report' => $report];
    }

    /** Il file deve essere un .xlsx dentro la cartella degli import (niente percorsi arbitrari). */
    private function controllaFile(string $file): void
    {
        $vero = realpath($file);
        $cartella = realpath(self::cartella());
        if ($vero === false || $cartella === false || ! str_starts_with($vero, $cartella.DIRECTORY_SEPARATOR)
            || strtolower(pathinfo($vero, PATHINFO_EXTENSION)) !== 'xlsx') {
            throw new RuntimeException('File non valido: deve essere un .xlsx caricato dal pannello.');
        }
    }
}
