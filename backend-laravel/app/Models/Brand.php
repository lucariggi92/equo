<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Marche.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Brand extends Model
{
    use SolaLettura;

    protected $table = 'brands';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function modelli(): HasMany
    {
        return $this->hasMany(CarModel::class, 'marca_id');
    }
}
