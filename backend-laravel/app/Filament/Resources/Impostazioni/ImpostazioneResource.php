<?php

namespace App\Filament\Resources\Impostazioni;

use App\Filament\Resources\Impostazioni\Pages\ElencoImpostazioni;
use App\Models\Setting;
use App\Services\Gestione\Modifiche;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Impostazioni (IVA, soglie del verdetto, ore minime, regole della taratura).
 * Dal task L8 i valori numerici si modificano qui, con limiti, conferma e registro (App\Services\Gestione\Modifiche).
 * Non si creano né si cancellano impostazioni.
 */
class ImpostazioneResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $slug = 'impostazioni';

    protected static ?string $modelLabel = 'impostazione';

    protected static ?string $pluralModelLabel = 'impostazioni';

    protected static ?string $navigationLabel = 'Impostazioni';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('chiave')
            ->columns([
                TextColumn::make('chiave')->label('Impostazione'),
                TextColumn::make('valore')->label('Valore'),
                TextColumn::make('descrizione')->label('Cosa regola')->wrap(),
            ])
            ->paginated(false)
            ->recordActions([self::azioneModifica()])
            ->toolbarActions([]);
    }

    /** Modifica di un valore: limiti controllati, conferma, scrittura con equo_import e registro con il valore precedente. */
    private static function azioneModifica(): Action
    {
        return Action::make('modifica')
            ->label('Modifica')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->visible(fn (Setting $record) => array_key_exists($record->chiave, Modifiche::IMPOSTAZIONI))
            ->modalHeading(fn (Setting $record) => Modifiche::IMPOSTAZIONI[$record->chiave]['nome'] ?? $record->chiave)
            ->modalDescription('Il nuovo valore vale subito per tutte le stime. La modifica finisce nel registro con il valore precedente.')
            ->fillForm(fn (Setting $record) => ['valore' => $record->valore])
            ->schema(fn (Setting $record) => [
                TextInput::make('valore')->label('Nuovo valore')->required()->numeric()
                    ->minValue(Modifiche::IMPOSTAZIONI[$record->chiave]['min'] ?? null)
                    ->maxValue(Modifiche::IMPOSTAZIONI[$record->chiave]['max'] ?? null),
            ])
            ->modalSubmitActionLabel('Salva')
            ->action(function (Setting $record, array $data, Action $action) {
                try {
                    app(Modifiche::class)->impostazione($record->chiave, (string) $data['valore']);
                } catch (ValidationException $e) {
                    Notification::make()->title('Valore non salvato')->body(collect($e->errors())->flatten()->first())->danger()->send();
                    $action->halt();
                }
                Notification::make()->title('Impostazione salvata')->success()->send();
            });
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
        return ['index' => ElencoImpostazioni::route('/')];
    }
}
