<?php

namespace App\Filament\Resources\Registro;

use App\Filament\Resources\Registro\Pages\ElencoRegistro;
use App\Models\AdminAuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro delle azioni dell'area riservata: sola lettura. Nessuno può modificarlo o cancellarlo
 * (né da qui, né dal codice, né da MySQL: l'utente equo_import ha solo SELECT e INSERT).
 */
class RegistroResource extends Resource
{
    protected static ?string $model = AdminAuditLog::class;

    protected static ?string $slug = 'registro';

    protected static ?string $modelLabel = 'azione';

    protected static ?string $pluralModelLabel = 'registro delle azioni';

    protected static ?string $navigationLabel = 'Registro azioni';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('utente'))
            ->defaultSort('creato_il', 'desc')
            ->columns([
                TextColumn::make('creato_il')->label('Quando')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('azione')->label('Azione'),
                TextColumn::make('oggetto')->label('Su cosa'),
                TextColumn::make('esito')->label('Esito')->badge()
                    ->color(fn (string $state) => $state === 'riuscita' ? 'success' : 'danger'),
                TextColumn::make('utente.email')->label('Account')->default('—'),
                TextColumn::make('indirizzo_ip')->label('Indirizzo'),
                TextColumn::make('dettagli')->label('Dettagli')->limit(80)->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state),
                TextColumn::make('user_agent')->label('Browser')->limit(60)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('esito')->label('Esito')->options(['riuscita' => 'Riuscita', 'fallita' => 'Fallita']),
                SelectFilter::make('azione')->label('Azione')->options(fn () => AdminAuditLog::query()
                    ->distinct()->orderBy('azione')->pluck('azione', 'azione')->all()),
            ])
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
        return ['index' => ElencoRegistro::route('/')];
    }
}
