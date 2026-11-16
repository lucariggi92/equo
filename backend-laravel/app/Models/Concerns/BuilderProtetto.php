<?php

namespace App\Models\Concerns;

use App\Exceptions\ModificaNonPermessa;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query builder che rifiuta le scritture di massa (es. PartPrice::where(...)->update(...)),
 * che in Eloquent non passano dagli eventi del modello.
 *
 * Con $permettiInserimento = true accetta solo gli inserimenti (tabelle "solo aggiunta":
 * richieste degli utenti, registro delle azioni); modifiche e cancellazioni restano vietate.
 */
class BuilderProtetto extends Builder
{
    public bool $permettiInserimento = false;

    private const INSERIMENTI = ['insert', 'insertgetid', 'insertorignore', 'insertorignorereturning', 'insertusing', 'insertorignoreusing'];

    private const SEMPRE_VIETATI = ['truncate', 'updatefrom', 'updateorinsert'];

    public function update(array $values)
    {
        $this->rifiuta('update');
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->rifiuta('upsert');
    }

    public function touch($column = null)
    {
        $this->rifiuta('touch');
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $this->rifiuta('increment');
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $this->rifiuta('decrement');
    }

    public function incrementEach(array $columns, array $extra = [])
    {
        $this->rifiuta('incrementEach');
    }

    public function decrementEach(array $columns, array $extra = [])
    {
        $this->rifiuta('decrementEach');
    }

    public function delete()
    {
        $this->rifiuta('delete');
    }

    public function forceDelete()
    {
        $this->rifiuta('forceDelete');
    }

    public function __call($method, $parameters)
    {
        $m = strtolower($method);
        if (in_array($m, self::SEMPRE_VIETATI, true) || (in_array($m, self::INSERIMENTI, true) && ! $this->permettiInserimento)) {
            $this->rifiuta($method);
        }

        return parent::__call($method, $parameters);
    }

    private function rifiuta(string $operazione): never
    {
        throw ModificaNonPermessa::per($this->model::class, $operazione);
    }
}
