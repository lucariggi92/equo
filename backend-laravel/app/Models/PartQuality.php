<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per ogni versione e componente: il codice del ricambio in ciascuna qualità.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class PartQuality extends Model
{
    use SolaLettura;

    protected $table = 'part_qualities';

    // chiave composta: Eloquent non la gestisce, ma per la sola lettura non serve
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    public function variante(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variante_id');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }

    public function ricambio(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'ricambio_id');
    }
}
