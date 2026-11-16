<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;

/**
 * Le fonti: ogni dato ha la sua, con il grado di affidabilità.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Source extends Model
{
    use SolaLettura;

    protected $table = 'sources';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'data_rilevazione' => 'date',
        ];
    }
}
