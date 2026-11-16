<?php

namespace App\Models\Concerns;

use App\Exceptions\ModificaNonPermessa;

/**
 * Tabelle in cui si può solo aggiungere: le richieste anonime degli utenti e il registro delle azioni.
 * Una riga, una volta scritta, non si modifica e non si cancella.
 * Lo stesso vincolo è nei permessi MySQL (equo_api: solo INSERT su quote_requests;
 * equo_import: solo SELECT e INSERT su admin_audit_log).
 */
trait SoloAggiunta
{
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw ModificaNonPermessa::per(static::class, 'modifica');
        }

        return parent::save($options);
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
        $builder = new BuilderProtetto($query);
        $builder->permettiInserimento = true;

        return $builder;
    }
}
