<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lanciata quando il codice prova a modificare dati che, da quella parte dell'app, sono in sola lettura
 * (strato 3 dei sei della decisione D8). Il database rifiuterebbe comunque (strato 4): questa eccezione
 * ferma il tentativo prima, con un messaggio chiaro.
 */
class ModificaNonPermessa extends RuntimeException
{
    public static function per(string $classe, string $operazione): self
    {
        return new self(sprintf(
            '%s è in sola lettura: operazione "%s" non permessa. I dati entrano solo dall\'import.',
            class_basename($classe),
            $operazione,
        ));
    }
}
