<?php

namespace App\Filament\Resources\Richieste;

use App\Filament\Resources\Richieste\Pages\ElencoRichieste;
use App\Models\QuoteRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Richieste ricevute dal sito (anonime): sola lettura. Si leggono con l'utente equo_import,
 * perché l'utente dell'API (equo_api) le può solo aggiungere, non leggere.
 */
class RichiestaResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $slug = 'richieste';

    protected static ?string $modelLabel = 'richiesta';

    protected static ?string $pluralModelLabel = 'richieste ricevute';

    protected static ?string $navigationLabel = 'Richieste ricevute';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return QuoteRequest::on('import')->with(['variante.modello', 'intervento', 'regione']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('creata_il', 'desc')
            ->columns([
                TextColumn::make('creata_il')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('variante.modello.nome')->label('Modello'),
                TextColumn::make('variante.etichetta')->label('Versione')->toggleable(),
                TextColumn::make('intervento.nome')->label('Intervento')
                    ->default(fn (QuoteRequest $r) => $r->altro_intervento ? "Altro: {$r->altro_intervento}" : '—'),
                TextColumn::make('qualita')->label('Qualità')->toggleable(),
                TextColumn::make('regione.nome')->label('Regione'),
                TextColumn::make('tipo_officina')->label('Officina'),
                TextColumn::make('importo_preventivo')->label('Preventivo')->money('EUR', locale: 'it')
                    ->description(fn (QuoteRequest $r) => $r->iva_inclusa ? 'IVA inclusa' : 'senza IVA'),
                TextColumn::make('stima_calcolata')->label('Stima EQUO')->money('EUR', locale: 'it'),
                TextColumn::make('scostamento_pct')->label('Scostamento')->suffix('%'),
                TextColumn::make('esito')->label('Esito')->badge()->color(fn (?string $state) => match ($state) {
                    'equo' => 'success', 'sopra_media' => 'warning', 'anomalo' => 'danger', default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('esito')->label('Esito')->options([
                    'equo' => 'Equo', 'sopra_media' => 'Sopra la media', 'anomalo' => 'Anomalo',
                    'dati_insufficienti' => 'Dati insufficienti', 'non_calcolabile' => 'Non calcolabile',
                ]),
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
        return ['index' => ElencoRichieste::route('/')];
    }
}
