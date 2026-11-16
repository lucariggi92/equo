<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Copia i dati delle 19 tabelle di EQUO dal database vero (equo) al database di prova (equo_test),
 * così i test automatici leggono dati veri senza poter toccare quelli originali.
 *
 * Usa l'utente equo_schema (collegamento 'schema'), l'unico con accesso a entrambi i database.
 * Rifiuta di partire se la destinazione non è equo_test.
 */
#[Signature('equo:copia-dati-test')]
#[Description('Copia i dati di equo nel database di prova equo_test (solo le 19 tabelle dei dati)')]
class CopiaDatiTest extends Command
{
    public const ORIGINE = 'equo';

    public const DESTINAZIONE = 'equo_test';

    /** In ordine di dipendenza: prima le tabelle a cui puntano le altre. */
    public const TABELLE = [
        'settings', 'regions', 'sources', 'brands', 'car_models', 'variants', 'interventions', 'components',
        'intervention_components', 'spare_parts', 'part_qualities', 'part_prices', 'price_samples', 'reference_prices',
        'labor_hours', 'labor_rates', 'markups', 'market_benchmarks', 'quote_requests',
    ];

    public function handle(): int
    {
        $db = DB::connection('schema');
        if ($db->getDatabaseName() !== self::DESTINAZIONE) {
            $this->error('Il collegamento "schema" punta a "'.$db->getDatabaseName().'": la copia parte solo verso '.self::DESTINAZIONE.'.');
            $this->line('Impostare DB_SCHEMA_DATABASE=equo_test (lo fa phpunit.xml per i test).');

            return self::FAILURE;
        }

        $db->statement('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach (array_reverse(self::TABELLE) as $t) {
                $db->statement("DELETE FROM `{$t}`");
            }
            foreach (self::TABELLE as $t) {
                // le colonne calcolate (es. "corrente") non si copiano: MySQL le ricalcola
                $colonne = collect($db->select(
                    'SELECT column_name AS c FROM information_schema.columns
                     WHERE table_schema = ? AND table_name = ? AND extra NOT LIKE ? AND extra NOT LIKE ?
                     ORDER BY ordinal_position',
                    [self::ORIGINE, $t, '%STORED GENERATED%', '%VIRTUAL GENERATED%']
                ))->map(fn ($r) => '`'.$r->c.'`')->implode(', ');
                $n = $db->affectingStatement(
                    "INSERT INTO `{$t}` ({$colonne}) SELECT {$colonne} FROM `".self::ORIGINE."`.`{$t}`"
                );
                $this->line(sprintf('  %-25s %5d righe', $t, $n));
            }
        } finally {
            $db->statement('SET FOREIGN_KEY_CHECKS = 1');
        }
        $this->info('Copia completata in '.self::DESTINAZIONE.'.');

        return self::SUCCESS;
    }
}
