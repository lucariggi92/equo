<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Intervention;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/opzioni — gli elenchi del form: marche con i loro modelli, interventi, qualità, tipi di officina, regioni.
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

    public function __invoke(): JsonResponse
    {
        $soloCompleti = config('equo.solo_modelli_completi');

        $marche = Brand::query()
            ->with(['modelli' => fn ($q) => $q->when($soloCompleti, fn ($q) => $q->where('info_completed', true))->orderBy('nome')])
            ->orderBy('nome')
            ->get()
            ->filter(fn (Brand $marca) => $marca->modelli->isNotEmpty())
            ->map(fn (Brand $marca) => [
                'id' => $marca->id,
                'nome' => $marca->nome,
                'modelli' => $marca->modelli->map(fn ($m) => ['id' => $m->id, 'nome' => $m->nome])->values(),
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
        ]);
    }

    /** @return list<array{valore: string, etichetta: string}> */
    private static function elenco(array $voci): array
    {
        return array_map(fn ($valore, $etichetta) => ['valore' => $valore, 'etichetta' => $etichetta], array_keys($voci), $voci);
    }
}
