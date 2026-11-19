<?php

namespace App\Filament\Resources\Modelli;

use App\Filament\Resources\Modelli\Pages\ElencoModelli;
use App\Models\CarModel;
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
 * Modelli di auto e loro presenza nel sito (task L8): nel form pubblico compaiono solo i modelli "pronti"
 * (info_completed). L'unica modifica possibile da qui è accenderli o spegnerli, con conferma e registro.
 */
class ModelloResource extends Resource
{
    protected static ?string $model = CarModel::class;

    protected static ?string $slug = 'modelli';

    protected static ?string $modelLabel = 'modello';

    protected static ?string $pluralModelLabel = 'modelli';

    protected static ?string $navigationLabel = 'Modelli nel sito';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('marca')->withCount('varianti'))
            ->defaultSort('nome')
            ->columns([
                TextColumn::make('marca.nome')->label('Marca'),
                TextColumn::make('nome')->label('Modello'),
                TextColumn::make('varianti_count')->label('Versioni'),
                IconColumn::make('info_completed')->label('Nel sito')->boolean(),
            ])
            ->paginated(false)
            ->recordActions([
                Action::make('accendi')
                    ->label('Metti nel sito')
                    ->icon(Heroicon::OutlinedEye)
                    ->visible(fn (CarModel $record) => ! $record->info_completed)
                    ->requiresConfirmation()
                    ->modalHeading(fn (CarModel $record) => "Mettere {$record->nome} nel sito?")
                    ->modalDescription('Il modello comparirà subito nel form pubblico. Controlla prima nella pagina iniziale che i suoi interventi siano calcolabili.')
                    ->action(fn (CarModel $record) => self::cambia($record, true)),
                Action::make('spegni')
                    ->label('Togli dal sito')
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('danger')
                    ->visible(fn (CarModel $record) => (bool) $record->info_completed)
                    ->requiresConfirmation()
                    ->modalHeading(fn (CarModel $record) => "Togliere {$record->nome} dal sito?")
                    ->action(fn (CarModel $record) => self::cambia($record, false)),
            ])
            ->toolbarActions([]);
    }

    private static function cambia(CarModel $modello, bool $pronto): void
    {
        app(Modifiche::class)->modelloPronto($modello->id, $pronto);
        Notification::make()->title($pronto ? "{$modello->nome} è nel sito" : "{$modello->nome} non è più nel sito")->success()->send();
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
        return ['index' => ElencoModelli::route('/')];
    }
}
