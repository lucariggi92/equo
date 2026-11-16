<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    private static bool $datiCopiati = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Sicurezza: i test girano solo sul database di prova. Se un collegamento puntasse al database vero, si fermano.
        foreach (['mysql', 'import', 'schema'] as $collegamento) {
            $nome = DB::connection($collegamento)->getDatabaseName();
            if ($nome !== 'equo_test') {
                throw new RuntimeException("Test fermati: il collegamento '{$collegamento}' punta a '{$nome}', non a equo_test.");
            }
        }

        // Una volta per esecuzione: copia i dati veri in equo_test, così i test leggono la Panda e gli altri modelli.
        if (! self::$datiCopiati) {
            Artisan::call('equo:copia-dati-test');
            self::$datiCopiati = true;
        }
    }
}
