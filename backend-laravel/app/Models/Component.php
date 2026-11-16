<?php

namespace App\Models;

use App\Models\Concerns\SolaLettura;
use Illuminate\Database\Eloquent\Model;

/**
 * Componenti generici (filtro olio, disco freno...).
 * In sola lettura: i dati entrano solo dall'import (vedi Concerns\SolaLettura).
 */
class Component extends Model
{
    use SolaLettura;

    protected $table = 'components';

    public $timestamps = false;

    protected $guarded = ['*'];
}
