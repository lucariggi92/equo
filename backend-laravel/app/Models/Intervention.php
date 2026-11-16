<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interventi del form: pacchetti (tagliando) e pezzi singoli.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Intervention extends Model
{
    use SolaLettura;

    protected $table = 'interventions';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'visibile_nel_form' => 'boolean',
        ];
    }

    public function composizione(): HasMany
    {
        return $this->hasMany(InterventionComponent::class, 'intervento_id');
    }
}
