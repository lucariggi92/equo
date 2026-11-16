<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Prezzi dei ricambi con storico; il corrente ha valido_al vuoto.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class PartPrice extends Model
{
    use SolaLettura;

    protected $table = 'part_prices';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'prezzo_netto' => 'decimal:4',
            'prezzo_min' => 'decimal:4',
            'prezzo_max' => 'decimal:4',
            'da_avvalorare' => 'boolean',
            'data_rilevazione' => 'date',
            'valido_dal' => 'date',
            'valido_al' => 'date',
        ];
    }

    /** Solo i valori in uso oggi (valido_al vuoto). */
    public function scopeCorrenti(Builder $query): Builder
    {
        return $query->whereNull('valido_al');
    }

    public function ricambio(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'ricambio_id');
    }

    public function campioni(): HasMany
    {
        return $this->hasMany(PriceSample::class, 'prezzo_id');
    }
}
