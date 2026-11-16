<?php

namespace App\Models;

use App\Models\Concerns\SoloAggiunta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro delle azioni dell'area riservata. Si può solo aggiungere (anche MySQL vieta modifica e cancellazione).
 * Solo aggiunta: le righe non si modificano né si cancellano (vedi Concerns\SoloAggiunta).
 */
class AdminAuditLog extends Model
{
    use SoloAggiunta;

    protected $connection = 'import';

    protected $table = 'admin_audit_log';

    public $timestamps = false;

    protected $fillable = ['user_id', 'azione', 'oggetto', 'dettagli', 'esito', 'indirizzo_ip', 'user_agent'];

    protected function casts(): array
    {
        return [
            'dettagli' => 'array',
            'creato_il' => 'datetime',
        ];
    }

    public function utente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
