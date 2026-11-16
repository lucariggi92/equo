<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Le 20 regioni italiane, con l'area (nord, centro, sud).
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Region extends Model
{
    use SolaLettura;

    protected $table = 'regions';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function tariffe(): HasMany
    {
        return $this->hasMany(LaborRate::class, 'regione_id');
    }
}
