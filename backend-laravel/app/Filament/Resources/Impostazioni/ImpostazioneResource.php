<?php

namespace App\Filament\Resources\Impostazioni;

use App\Filament\Resources\Impostazioni\Pages\ElencoImpostazioni;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Impostazioni (IVA, soglie del verdetto, ore minime, regole della taratura): per ora in sola lettura.
 * La modifica, con conferma e registro, arriva con il task L8.
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
            ->recordActions([])
            ->toolbarActions([]);
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
