<?php

namespace App\Filament\Widgets;

use App\Models\CarModel;
use App\Models\LaborHour;
use App\Models\LaborRate;
use App\Models\PartPrice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Pagina iniziale dell'area riservata: lo stato dei dati in numeri (sola lettura).
 */
class StatoDati extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Stato dei dati';

    protected function getStats(): array
    {
        $prezzi = PartPrice::correnti()->selectRaw('metodo, COUNT(*) AS n')->groupBy('metodo')->pluck('n', 'metodo');
        $totPrezzi = $prezzi->sum();
        $verificati = (int) ($prezzi['rilevato'] ?? 0);
        $unNegozio = (int) ($prezzi['indicativo'] ?? 0);
        $ore = LaborHour::selectRaw('origine, COUNT(*) AS n')->groupBy('origine')->pluck('n', 'origine');
        $richieste = DB::connection('import')->table('quote_requests');

        return [
            Stat::make('Prezzi verificati su 2 negozi', "{$verificati} su {$totPrezzi}")
                ->description("{$unNegozio} da un solo negozio, ".($totPrezzi - $verificati - $unNegozio).' stimati')
                ->color($verificati < $totPrezzi / 2 ? 'danger' : 'success'),
            Stat::make('Ore di manodopera da tempario', (int) ($ore['tempario'] ?? 0).' su '.$ore->sum())
                ->description((int) ($ore['assunzione'] ?? 0).' ancora ipotesi iniziali (D6)'),
            Stat::make('Tariffe orarie confermate', LaborRate::correnti()->where('origine', 'reale')->count().' su '.LaborRate::correnti()->count())
                ->description('le altre sono ipotesi dichiarate (D6)')
                ->color('warning'),
            Stat::make('Modelli pronti per il form', CarModel::where('info_completed', true)->count().' su '.CarModel::count())
                ->description('solo questi compaiono nel sito pubblico'),
            Stat::make('Richieste ricevute', (clone $richieste)->count())
                ->description((clone $richieste)->where('creata_il', '>=', now()->subDays(7))->count().' negli ultimi 7 giorni'),
        ];
    }
}
