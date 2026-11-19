<?php

namespace App\Filament\Resources\Interventi;

use App\Filament\Resources\Interventi\Pages\ElencoInterventi;
use App\Models\Intervention;
use App\Services\Gestione\Modifiche;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Interventi del form pubblico (task L8): si possono solo mostrare o nascondere, con conferma e registro.
 * Nascondere non cancella niente: composizione, ore e prezzi restano.
 */
class InterventoResource extends Resource
{
    protected static ?string $model = Intervention::class;

    protected static ?string $slug = 'interventi';

    protected static ?string $modelLabel = 'intervento';

    protected static ?string $pluralModelLabel = 'interventi';

    protected static ?string $navigationLabel = 'Interventi nel sito';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('nome')->label('Intervento'),
                TextColumn::make('descrizione')->label('Cosa comprende')->wrap(),
                TextColumn::make('tipo')->label('Tipo'),
                IconColumn::make('visibile_nel_form')->label('Nel sito')->boolean(),
            ])
            ->paginated(false)
            ->recordActions([
                Action::make('mostra')
                    ->label('Mostra nel sito')
                    ->icon(Heroicon::OutlinedEye)
                    ->visible(fn (Intervention $record) => ! $record->visibile_nel_form)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Intervention $record) => "Mostrare \"{$record->nome}\" nel sito?")
                    ->action(fn (Intervention $record) => self::cambia($record, true)),
                Action::make('nascondi')
                    ->label('Nascondi dal sito')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('danger')
                    ->visible(fn (Intervention $record) => (bool) $record->visibile_nel_form)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Intervention $record) => "Nascondere \"{$record->nome}\" dal sito?")
                    ->modalDescription('Non si cancella niente: composizione, ore e prezzi restano e si può rimostrare in ogni momento.')
                    ->action(fn (Intervention $record) => self::cambia($record, false)),
            ])
            ->toolbarActions([]);
    }

    private static function cambia(Intervention $intervento, bool $visibile): void
    {
        app(Modifiche::class)->interventoVisibile($intervento->id, $visibile);
        Notification::make()->title($visibile ? "\"{$intervento->nome}\" è nel sito" : "\"{$intervento->nome}\" è nascosto")->success()->send();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ElencoInterventi::route('/')];
    }
}
