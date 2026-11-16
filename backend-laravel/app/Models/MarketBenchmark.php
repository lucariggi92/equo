<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Valori di mercato pubblicati (tariffe, prezzi al cliente) per la taratura.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class MarketBenchmark extends Model
{
    use SolaLettura;

    protected $table = 'market_benchmarks';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'valore_min' => 'decimal:2',
            'valore_max' => 'decimal:2',
            'iva_inclusa' => 'boolean',
            'data_rilevazione' => 'date',
        ];
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fonte_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variante_id');
    }

    public function intervento(): BelongsTo
    {
        return $this->belongsTo(Intervention::class, 'intervento_id');
    }
}
