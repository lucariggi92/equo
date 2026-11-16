<?php

namespace App\Services\Stima;

use App\Models\Intervention;
use App\Models\Variant;

/**
 * Interventi che non esistono su una certa versione dell'auto: niente frizione con il cambio automatico,
 * niente cinghia con la distribuzione a catena. Stessa regola del controllo di copertura MySQL.
 * Se il dato tecnico manca, l'intervento resta previsto.
 */
final class InterventiPrevisti
{
    /** @return list<int> id degli interventi da non proporre per questa versione */
    public function nonPrevisti(Variant $variante): array
    {
        $nomi = [];
        if ($variante->cambio === 'automatico') {
            $nomi[] = 'Frizione';
        }
        if ($variante->distribuzione === 'catena') {
            $nomi[] = 'Cinghia distribuzione';
        }

        return $nomi === [] ? [] : Intervention::whereIn('nome', $nomi)->orderBy('id')->pluck('id')->all();
    }

    public function previsto(Variant $variante, int $interventoId): bool
    {
        return ! in_array($interventoId, $this->nonPrevisti($variante), true);
    }
}
