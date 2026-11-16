<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelli di auto.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class CarModel extends Model
{
    use SolaLettura;

    protected $table = 'car_models';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'info_completed' => 'boolean',
        ];
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'marca_id');
    }

    public function varianti(): HasMany
    {
        return $this->hasMany(Variant::class, 'modello_id');
    }
}
