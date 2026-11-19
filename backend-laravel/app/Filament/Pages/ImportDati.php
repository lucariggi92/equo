<?php

namespace App\Filament\Pages;

use App\Services\Manutenzione\Import;
use App\Services\Registro;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Throwable;

/**
 * Import dei dati dal file Excel (task L8), in due passi:
 *   1. carichi il file e parte una prova: tutto controllato, niente salvato, vedi il report;
 *   2. se la prova è andata bene confermi: backup automatico, poi import vero (tutto o niente).
 * Ogni prova e ogni import finiscono nel registro.
 */
class ImportDati extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Import dati';

    protected static ?string $title = 'Import dei dati dal file Excel';

    protected static ?string $slug = 'import';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.import-dati';

    /** Percorso del file caricato: non modificabile dal browser. */
    #[Locked]
    public ?string $file = null;

    #[Locked]
    public ?string $nomeFile = null;

    #[Locked]
    public ?string $report = null;

    #[Locked]
    public bool $provaRiuscita = false;

    #[Locked]
    public bool $caricato = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prova')
                ->label($this->file ? 'Carica un altro file' : 'Carica il file e fai la prova')
                ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                ->modalHeading('Prova dell\'import')
                ->modalDescription('Il file viene controllato riga per riga e caricato in prova: alla fine si annulla tutto. Niente viene salvato.')
                ->schema([
                    FileUpload::make('file')->label('File Excel dei dati (.xlsx)')
                        ->disk('local')->directory('import')->visibility('private')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream',
                        ])
                        ->maxSize(config('equo.import.dimensione_massima_kb'))
                        ->storeFileNamesIn('nome_originale')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Fai la prova')
                ->action(fn (array $data) => $this->prova($data)),

            Action::make('conferma')
                ->label('Conferma l\'import')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $this->file !== null && $this->provaRiuscita && ! $this->caricato)
                ->requiresConfirmation()
                ->modalHeading('Caricare davvero i dati?')
                ->modalDescription('Prima parte un backup automatico del database; poi i dati del file entrano in un\'unica operazione: o tutto o niente.')
                ->modalSubmitActionLabel('Carica i dati')
                ->action(fn () => $this->carica()),

            Action::make('annulla')
                ->label('Annulla')
                ->color('gray')
                ->visible(fn () => $this->file !== null && ! $this->caricato)
                ->action(fn () => $this->pulisci()),
        ];
    }

    private function prova(array $data): void
    {
        $this->pulisci();
        $this->file = Storage::disk('local')->path($data['file']);
        $this->nomeFile = (string) ($data['nome_originale'] ?? basename($data['file']));
        try {
            $r = app(Import::class)->prova($this->file);
        } catch (Throwable $e) {
            report($e);
            Registro::scrivi('import_prova', $this->nomeFile, ['errore' => $e->getMessage()], 'fallita');
            Notification::make()->title('Prova non riuscita')->body($e->getMessage())->danger()->send();
            $this->pulisci();

            return;
        }
        $this->report = $r['report'];
        $this->provaRiuscita = $r['ok'];
        Registro::scrivi('import_prova', $this->nomeFile, ['esito_database' => $r['ok'] ? 'OK' : 'ERRORE'], $r['ok'] ? 'riuscita' : 'fallita');
        Notification::make()
            ->title($r['ok'] ? 'Prova riuscita: niente è stato salvato' : 'La prova ha trovato un errore: niente è stato salvato')
            ->body($r['ok'] ? 'Leggi il report e, se va bene, conferma l\'import.' : 'Leggi il report per capire cosa correggere nel file.')
            ->{$r['ok'] ? 'success' : 'danger'}()->send();
    }

    private function carica(): void
    {
        try {
            $r = app(Import::class)->carica($this->file);
        } catch (Throwable $e) {
            report($e);
            Registro::scrivi('import_confermato', $this->nomeFile, ['errore' => $e->getMessage()], 'fallita');
            Notification::make()->title('Import non riuscito')->body($e->getMessage())->danger()->send();

            return;
        }
        $this->report = $r['report'];
        $this->caricato = $r['ok'];
        Registro::scrivi('import_confermato', $this->nomeFile, ['backup_prima' => $r['backup'], 'esito_database' => $r['ok'] ? 'OK' : 'ERRORE'],
            $r['ok'] ? 'riuscita' : 'fallita');
        File::delete($this->file);
        Notification::make()
            ->title($r['ok'] ? 'Dati caricati' : 'Import non riuscito: niente è stato salvato')
            ->body("Backup fatto prima dell'import: {$r['backup']}")
            ->{$r['ok'] ? 'success' : 'danger'}()->send();
    }

    private function pulisci(): void
    {
        if ($this->file !== null && str_starts_with((string) realpath($this->file), (string) realpath(Import::cartella()))) {
            File::delete($this->file);
        }
        $this->file = $this->nomeFile = $this->report = null;
        $this->provaRiuscita = $this->caricato = false;
    }
}
