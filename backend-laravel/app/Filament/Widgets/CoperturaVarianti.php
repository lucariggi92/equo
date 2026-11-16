<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Copertura per versione e qualità del ricambio: quanti interventi sono calcolabili e quanta parte del valore
 * dei ricambi è verificata, da un solo negozio o stimata. Usa la procedura MySQL controlli_copertura (sola lettura).
 */
class CoperturaVarianti extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Copertura dei 10 modelli del pilota')
            ->records(fn () => $this->righe())
            ->columns([
                TextColumn::make('modello')->label('Modello'),
                TextColumn::make('variante')->label('Versione'),
                TextColumn::make('qualita')->label('Qualità'),
                TextColumn::make('calcolabili')->label('Interventi calcolabili'),
                TextColumn::make('valore_verificato_pct')->label('Valore verificato')->suffix('%'),
                TextColumn::make('valore_indicativo_pct')->label('Da un solo negozio')->suffix('%'),
                TextColumn::make('valore_stimato_pct')->label('Stimato')->suffix('%'),
            ])
            ->paginated(false);
    }

    /** @return array<string, array<string, mixed>> */
    private function righe(): array
    {
        try {
            $st = DB::connection('import')->getPdo()->prepare('CALL controlli_copertura(180)');
            $st->execute();
        } catch (\PDOException $e) {
            // la procedura sta nel database dei dati (database-mysql/queries); se manca, la tabella resta vuota
            report($e);

            return [];
        }
        $righe = $st->fetchAll(PDO::FETCH_ASSOC);
        while ($st->nextRowset()) {
            // gli altri risultati della procedura non servono qui
        }
        $st->closeCursor();

        $out = [];
        foreach ($righe as $i => $r) {
            $out["r{$i}"] = $r + ['calcolabili' => "{$r['interventi_calcolabili']} su {$r['interventi_totali']}"];
        }

        return $out;
    }
}
