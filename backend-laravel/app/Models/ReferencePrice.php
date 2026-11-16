<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prezzi di riferimento per marca e categoria: base delle stime dei prezzi mancanti.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class ReferencePrice extends Model
{
    use SolaLettura;

    protected $table = 'reference_prices';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'prezzo_netto' => 'decimal:4',
            'data_rilevazione' => 'date',
        ];
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fonte_id');
    }
}
