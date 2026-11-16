<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ricarico sui ricambi per tipo di officina e tipo di prezzo, con storico.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Markup extends Model
{
    use SolaLettura;

    protected $table = 'markups';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'fattore' => 'decimal:4',
            'valido_dal' => 'date',
            'valido_al' => 'date',
        ];
    }

    /** Solo i valori in uso oggi (valido_al vuoto). */
    public function scopeCorrenti(Builder $query): Builder
    {
        return $query->whereNull('valido_al');
    }
}
