<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tariffa oraria netta per regione e tipo di officina, con storico.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class LaborRate extends Model
{
    use SolaLettura;

    protected $table = 'labor_rates';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'tariffa_oraria_netta' => 'decimal:2',
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

    public function regione(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regione_id');
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fonte_id');
    }
}
