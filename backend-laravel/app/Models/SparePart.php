<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Codici dei ricambi (produttore + codice); i "generici" sono segnaposto stimati.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class SparePart extends Model
{
    use SolaLettura;

    protected $table = 'spare_parts';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'generico' => 'boolean',
        ];
    }

    public function prezzi(): HasMany
    {
        return $this->hasMany(PartPrice::class, 'ricambio_id');
    }

    public function prezzoCorrente(): HasOne
    {
        return $this->hasOne(PartPrice::class, 'ricambio_id')->whereNull('valido_al');
    }

    public function qualita(): HasMany
    {
        return $this->hasMany(PartQuality::class, 'ricambio_id');
    }
}
