<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ogni prezzo letto da un negozio, con la sua fonte.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class PriceSample extends Model
{
    use SolaLettura;

    protected $table = 'price_samples';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'prezzo_fonte' => 'decimal:4',
            'prezzo_netto' => 'decimal:4',
            'iva_fonte_pct' => 'decimal:2',
            'iva_inclusa' => 'boolean',
            'scartato' => 'boolean',
        ];
    }

    public function prezzo(): BelongsTo
    {
        return $this->belongsTo(PartPrice::class, 'prezzo_id');
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fonte_id');
    }
}
