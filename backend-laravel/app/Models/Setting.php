<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;

/**
 * Parametri globali: IVA, soglie del verdetto, ore minime, regole della taratura.
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Setting extends Model
{
    use SolaLettura;

    protected $table = 'settings';

    protected $primaryKey = 'chiave';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];
}
