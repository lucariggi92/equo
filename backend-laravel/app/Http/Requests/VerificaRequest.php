<?php

namespace App\Http\Requests;

use App\Models\Intervention;
use App\Models\Variant;
use App\Services\Stima\CalcoloStima;
use App\Services\Stima\InterventiPrevisti;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Controlli di POST /api/verifica (strato 2 della decisione D8): ogni campo deve esistere, essere del tipo giusto
 * e nell'intervallo ammesso. I campi non previsti vengono ignorati (il controller usa solo validated()).
 */
class VerificaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $annoMax = now()->year + 1;

        return [
            'variante_id' => ['required', 'integer', 'min:1', 'exists:variants,id'],
            'intervento_id' => ['required_without:altro_intervento', 'nullable', 'integer', 'min:1',
                Rule::exists('interventions', 'id')->where('visibile_nel_form', true)],
            'altro_intervento' => ['required_without:intervento_id', 'nullable', 'string', 'min:3', 'max:120'],
            'qualita' => ['required', Rule::in(CalcoloStima::QUALITA)],
            'regione_id' => ['required', 'integer', 'min:1', 'exists:regions,id'],
            'tipo_officina' => ['required', Rule::in(CalcoloStima::OFFICINE)],
            'importo_preventivo' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:'.config('equo.preventivo_massimo')],
            'iva_inclusa' => ['required', 'boolean'],
            'anno_immatricolazione' => ['nullable', 'integer', 'between:1980,'.$annoMax],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'variante_id.required' => 'Scegli la versione dell\'auto.',
            'variante_id.*' => 'Versione dell\'auto non valida.',
            'intervento_id.required_without' => 'Scegli l\'intervento o descrivilo.',
            'intervento_id.*' => 'Intervento non valido.',
            'altro_intervento.required_without' => 'Scegli l\'intervento o descrivilo.',
            'altro_intervento.*' => 'Descrivi l\'intervento in 3-120 caratteri.',
            'qualita.*' => 'Scegli la qualità del ricambio.',
            'regione_id.*' => 'Scegli la regione.',
            'tipo_officina.*' => 'Scegli il tipo di officina.',
            'importo_preventivo.required' => 'Scrivi l\'importo del preventivo.',
            'importo_preventivo.*' => 'L\'importo deve essere un numero maggiore di zero, con al massimo 2 decimali, fino a '
                .config('equo.preventivo_massimo').' euro.',
            'iva_inclusa.*' => 'Indica se l\'importo comprende l\'IVA.',
            'anno_immatricolazione.*' => 'L\'anno di immatricolazione deve essere tra 1980 e il prossimo anno.',
        ];
    }

    /** Controlli che dipendono da più campi insieme. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $variante = Variant::with('modello')->find($this->input('variante_id'));
            if (config('equo.solo_modelli_completi') && ! $variante->modello->info_completed) {
                $validator->errors()->add('variante_id', 'Questo modello non è ancora disponibile.');

                return;
            }
            $intervento = $this->input('intervento_id');
            if ($intervento !== null && ! app(InterventiPrevisti::class)->previsto($variante, (int) $intervento)) {
                $nome = Intervention::whereKey($intervento)->value('nome');
                $validator->errors()->add('intervento_id', "{$nome}: intervento non previsto per questa versione dell'auto.");
            }
            $anno = $this->input('anno_immatricolazione');
            if ($anno !== null && ($anno < $variante->anno_produzione_inizio
                    || ($variante->anno_produzione_fine !== null && $anno > $variante->anno_produzione_fine))) {
                $validator->errors()->add('anno_immatricolazione', 'Questa versione non era in produzione in quell\'anno: controlla la versione scelta.');
            }
        }];
    }
}
