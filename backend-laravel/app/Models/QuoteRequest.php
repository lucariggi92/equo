<?php

namespace App\Models;

use App\Models\Concerns\SoloAggiunta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Richieste anonime degli utenti (nessun dato personale). Si possono solo aggiungere.
 * Solo aggiunta: le righe non si modificano né si cancellano (vedi Concerns\SoloAggiunta).
 */
class QuoteRequest extends Model
{
    use SoloAggiunta;

    protected $table = 'quote_requests';

    public $timestamps = false;

    protected $fillable = ['variante_id', 'anno_immatricolazione', 'intervento_id', 'altro_intervento', 'qualita', 'regione_id', 'tipo_officina', 'importo_preventivo', 'iva_inclusa', 'stima_calcolata', 'scostamento_pct', 'esito'];

    protected function casts(): array
    {
        return [
            'creata_il' => 'datetime',
            'importo_preventivo' => 'decimal:2',
            'iva_inclusa' => 'boolean',
            'stima_calcolata' => 'decimal:2',
            'scostamento_pct' => 'decimal:2',
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

    public function regione(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'regione_id');
    }
}
