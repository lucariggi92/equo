<?php

namespace App\Services\Manutenzione;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Backup del database (task L8): stesso risultato di database-mysql/maintenance/backup.ps1, così
 * ripristina.ps1 funziona con entrambi. Per ogni backup:
 *   <database>-AAAAMMGG-HHMMSS.sql             dump completo (tabelle, dati, procedure)
 *   <database>-AAAAMMGG-HHMMSS.inventario.txt  righe di ogni tabella e nomi delle procedure, per verificare il ripristino
 * Usa l'utente MySQL equo_schema (lettura di tutto, procedure comprese) con un file di credenziali temporaneo,
 * creato e cancellato a ogni backup: la password non compare mai nella riga di comando.
 * I backup restano fuori dalla cartella pubblica del sito e non sono mai scaricabili dal web.
 */
class Backup
{
    public function cartella(): string
    {
        return config('equo.backup.cartella');
    }

    /** @return array{file: string, mb: float, tabelle: int, procedure: int} */
    public function esegui(): array
    {
        $db = DB::connection('schema');
        $nome = $db->getDatabaseName();
        File::ensureDirectoryExists($this->cartella());
        $base = $this->cartella().DIRECTORY_SEPARATOR.$nome.'-'.now()->format('Ymd-His');
        $sql = $base.'.sql';

        $cnf = $this->fileCredenziali();
        try {
            $esito = Process::timeout(600)->run([
                config('equo.backup.mysqldump'),
                '--defaults-extra-file='.$cnf,
                '--default-character-set=utf8mb4',
                '--single-transaction', '--routines', '--triggers', '--no-tablespaces',
                '--set-gtid-purged=OFF', '--hex-blob',
                '--result-file='.$sql,
                $nome,
            ]);
        } finally {
            File::delete($cnf);
        }
        if ($esito->failed()) {
            File::delete($sql);
            throw new RuntimeException('Backup non riuscito: '.Str::limit(trim($esito->errorOutput()), 300));
        }

        $inventario = $this->inventario();
        File::put($base.'.inventario.txt', implode("\n", $inventario)."\n");
        $this->pulizia($nome);

        return [
            'file' => basename($sql),
            'mb' => round(filesize($sql) / 1048576, 2),
            'tabelle' => count(array_filter($inventario, fn ($r) => str_starts_with($r, 'tabella'))),
            'procedure' => count(array_filter($inventario, fn ($r) => str_starts_with($r, 'procedura'))),
        ];
    }

    /** @return list<array{file: string, data: string, mb: float, tabelle: int, procedure: int}> */
    public function elenco(): array
    {
        $out = [];
        foreach (File::glob($this->cartella().DIRECTORY_SEPARATOR.'*.sql') ?: [] as $sql) {
            $inv = preg_replace('/\.sql$/', '.inventario.txt', $sql);
            $righe = File::exists($inv) ? file($inv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
            $out[] = [
                'file' => basename($sql),
                'data' => date('d/m/Y H:i', filemtime($sql)),
                'mb' => round(filesize($sql) / 1048576, 2),
                'tabelle' => count(array_filter($righe, fn ($r) => str_starts_with($r, 'tabella'))),
                'procedure' => count(array_filter($righe, fn ($r) => str_starts_with($r, 'procedura'))),
            ];
        }
        usort($out, fn ($a, $b) => strcmp($b['file'], $a['file']));

        return $out;
    }

    /** @return list<string> stesso formato di backup.ps1: "tabella<TAB>nome<TAB>righe" e "procedura<TAB>nome" */
    private function inventario(): array
    {
        $db = DB::connection('schema');
        $tabelle = collect($db->select("SELECT table_name AS t FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' ORDER BY table_name"))->pluck('t');
        $righe = $tabelle->map(fn ($t) => "tabella\t{$t}\t".$db->table($t)->count());
        $procedure = collect($db->select('SELECT routine_name AS r FROM information_schema.routines
            WHERE routine_schema = DATABASE() ORDER BY routine_name'))->map(fn ($p) => "procedura\t{$p->r}");

        return $righe->merge($procedure)->values()->all();
    }

    /** Tiene i backup degli ultimi N giorni, e comunque almeno gli ultimi 5. */
    private function pulizia(string $nome): void
    {
        $file = File::glob($this->cartella().DIRECTORY_SEPARATOR.$nome.'-*.sql') ?: [];
        rsort($file);
        $limite = now()->subDays(config('equo.backup.conserva_giorni'))->getTimestamp();
        foreach (array_slice($file, 5) as $vecchio) {
            if (filemtime($vecchio) < $limite) {
                File::delete([$vecchio, preg_replace('/\.sql$/', '.inventario.txt', $vecchio)]);
            }
        }
    }

    private function fileCredenziali(): string
    {
        $c = config('database.connections.schema');
        $percorso = storage_path('app/private/mysql-'.Str::uuid().'.cnf');
        File::ensureDirectoryExists(dirname($percorso));
        File::put($percorso, "[client]\nuser={$c['username']}\npassword=\"{$c['password']}\"\nhost={$c['host']}\nport={$c['port']}\n");
        @chmod($percorso, 0600);

        return $percorso;
    }
}
