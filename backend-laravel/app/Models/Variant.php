<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Versioni (motore e generazione): la chiave per ricambi, ore e composizione.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Variant extends Model
{
    use SolaLettura;

    protected $table = 'variants';

    public $timestamps = false;

    protected $guarded = ['*'];

    public function modello(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'modello_id');
    }

    public function composizione(): HasMany
    {
        return $this->hasMany(InterventionComponent::class, 'variante_id');
    }

    public function ricambiPerQualita(): HasMany
    {
        return $this->hasMany(PartQuality::class, 'variante_id');
    }

    public function ore(): HasMany
    {
        return $this->hasMany(LaborHour::class, 'variante_id');
    }
}
