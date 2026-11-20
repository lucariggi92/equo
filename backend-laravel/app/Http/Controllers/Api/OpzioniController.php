<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Intervention;
use App\Models\PartPrice;
use App\Models\Region;
use App\Services\Stima\InterventiPrevisti;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/opzioni — gli elenchi del form: marche con i loro modelli e le versioni di ogni modello, interventi,
 * qualità, tipi di officina, regioni. Le versioni sono qui perché il sito mostra un solo elenco di auto:
 * così basta una chiamata invece di una per modello (che con il limite di 120 letture al minuto sarebbe un rischio).
 */
class OpzioniController extends Controller
{
    public const QUALITA = [
        'originale' => 'Originale (ricambio della casa)',
        'equivalente' => 'Equivalente (stessa qualità, altra marca)',
        'economico' => 'Economico',
    ];

    public const TIPI_OFFICINA = [
        'concessionaria' => 'Concessionaria',
        'generica' => 'Officina generica',
        'catena' => 'Catena di assistenza',
    ];

    public function __invoke(InterventiPrevisti $previsti): JsonResponse
    {
        $soloCompleti = config('equo.solo_modelli_completi');

        $marche = Brand::query()
            ->with(['modelli' => fn ($q) => $q->when($soloCompleti, fn ($q) => $q->where('info_completed', true))->orderBy('nome'),
                'modelli.varianti' => fn ($q) => $q->orderBy('etichetta')])
            ->orderBy('nome')
            ->get()
            ->filter(fn (Brand $marca) => $marca->modelli->isNotEmpty())
            ->map(fn (Brand $marca) => [
                'id' => $marca->id,
                'nome' => $marca->nome,
                'modelli' => $marca->modelli->map(fn ($m) => [
                    'id' => $m->id,
                    'nome' => $m->nome,
                    'varianti' => $m->varianti->map(fn ($v) => VariantiController::formato($v, $previsti))->values(),
                ])->values(),
            ])
            ->values();

        return response()->json([
            'marche' => $marche,
            'interventi' => Intervention::where('visibile_nel_form', true)->orderBy('id')
                ->get(['id', 'nome', 'descrizione', 'tipo'])
                ->map(fn ($i) => ['id' => $i->id, 'nome' => $i->nome, 'descrizione' => $i->descrizione, 'tipo' => $i->tipo]),
            'qualita' => self::elenco(self::QUALITA),
            'tipi_officina' => self::elenco(self::TIPI_OFFICINA),
            'regioni' => Region::orderBy('nome')->get(['id', 'nome'])->map(fn ($r) => ['id' => $r->id, 'nome' => $r->nome]),
            // data del prezzo di ricambio in uso più recente: il report la mostra come "data dei dati" (task R6)
            'prezzi_ricambi_al' => PartPrice::correnti()->max('data_rilevazione'),
        ]);
    }

    /** @return list<array{valore: string, etichetta: string}> */
    private static function elenco(array $voci): array
    {
        return array_map(fn ($valore, $etichetta) => ['valore' => $valore, 'etichetta' => $etichetta], array_keys($voci), $voci);
    }
}
