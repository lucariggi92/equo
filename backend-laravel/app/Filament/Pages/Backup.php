<?php

namespace App\Filament\Pages;

use App\Services\Manutenzione\Backup as ServizioBackup;
use App\Services\Registro;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Throwable;

/**
 * Backup (task L8): elenco dei backup e backup immediato. Uno automatico parte ogni notte alle 02:30.
 * I file non si scaricano dal web e il ripristino si fa solo dal server (database-mysql/maintenance/ripristina.ps1):
 * dal pannello non si può sovrascrivere il database.
 */
class Backup extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Backup';

    protected static ?string $title = 'Backup del database';

    protected static ?string $slug = 'backup';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.backup';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => collect(app(ServizioBackup::class)->elenco())->keyBy('file')->all())
            ->columns([
                TextColumn::make('file')->label('File'),
                TextColumn::make('data')->label('Creato il'),
                TextColumn::make('mb')->label('Dimensione')->suffix(' MB'),
                TextColumn::make('tabelle')->label('Tabelle'),
                TextColumn::make('procedure')->label('Procedure'),
            ])
            ->emptyStateHeading('Nessun backup')
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup')
                ->label('Fai un backup adesso')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->requiresConfirmation()
                ->modalHeading('Backup del database')
                ->modalDescription('Copia completa di tabelle, dati e procedure, con l\'inventario per verificare il ripristino. Richiede pochi secondi.')
                ->modalSubmitActionLabel('Fai il backup')
                ->action(function () {
                    try {
                        $r = app(ServizioBackup::class)->esegui();
                    } catch (Throwable $e) {
                        report($e);
                        Registro::scrivi('backup', null, ['da' => 'area riservata'], 'fallita');
                        Notification::make()->title('Backup non riuscito')->body('Il dettaglio è nel log del server.')->danger()->send();

                        return;
                    }
                    Registro::scrivi('backup', $r['file'], $r + ['da' => 'area riservata']);
                    Notification::make()->title('Backup fatto')
                        ->body("{$r['file']}: {$r['tabelle']} tabelle, {$r['procedure']} procedure, {$r['mb']} MB")->success()->send();
                }),
        ];
    }
}
