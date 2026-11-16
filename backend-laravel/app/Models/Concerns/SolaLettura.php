<?php

namespace App\Models\Concerns;

use App\Exceptions\ModificaNonPermessa;

/**
 * Modelli del catalogo in sola lettura (strato 3 della decisione D8).
 *
 * Blocca ogni scrittura fatta con Eloquent: salvataggio, creazione, modifica, cancellazione,
 * anche nelle varianti "quiet" e nelle scritture di massa dal query builder.
 * I dati del catalogo entrano solo dall'import, che non usa questi modelli.
 * Se il codice provasse comunque a scrivere passando da sotto Eloquent, lo ferma MySQL:
 * l'utente equo_api non ha i permessi di modifica (strato 4).
 */
trait SolaLettura
{
    public function save(array $options = [])
    {
        throw ModificaNonPermessa::per(static::class, $this->exists ? 'modifica' : 'creazione');
    }

    public function delete()
    {
        throw ModificaNonPermessa::per(static::class, 'cancellazione');
    }

    public function forceDelete()
    {
        throw ModificaNonPermessa::per(static::class, 'cancellazione');
    }

    public function newEloquentBuilder($query)
    {
        return new BuilderProtetto($query);
    }
}
