<?php

namespace App\Http\Requests;

use App\Models\CarModel;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Controlli di GET /api/varianti: il modello deve esistere ed essere visibile nel form; l'anno è facoltativo.
 */
class VariantiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'modello_id' => ['required', 'integer', 'min:1', 'exists:car_models,id', $this->modelloVisibile()],
            'anno' => ['nullable', 'integer', 'between:1980,'.(now()->year + 1)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'modello_id.required' => 'Scegli il modello.',
            'modello_id.*' => 'Modello non valido.',
            'anno.*' => "L'anno deve essere tra 1980 e il prossimo anno.",
        ];
    }

    private function modelloVisibile(): Closure
    {
        return function (string $attributo, mixed $valore, Closure $errore) {
            if (config('equo.solo_modelli_completi') && ! CarModel::whereKey($valore)->where('info_completed', true)->exists()) {
                $errore('Questo modello non è ancora disponibile.');
            }
        };
    }
}
