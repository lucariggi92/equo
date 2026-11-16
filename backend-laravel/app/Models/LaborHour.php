<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ore di manodopera per versione e intervento (tempario o assunzione).
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class LaborHour extends Model
{
    use SolaLettura;

    protected $table = 'labor_hours';

    // chiave composta: Eloquent non la gestisce, ma per la sola lettura non serve
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'ore' => 'decimal:2',
        ];
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variante_id');
    }

    public function intervento(): BelongsTo
    {
        return $this->belongsTo(Intervention::class, 'intervento_id');
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'fonte_id');
    }
}
