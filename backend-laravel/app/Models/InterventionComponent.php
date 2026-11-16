<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per questa versione, in questo intervento, serve questo componente in questa quantità.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class InterventionComponent extends Model
{
    use SolaLettura;

    protected $table = 'intervention_components';

    // chiave composta: Eloquent non la gestisce, ma per la sola lettura non serve
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'quantita' => 'decimal:2',
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

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'componente_id');
    }
}
