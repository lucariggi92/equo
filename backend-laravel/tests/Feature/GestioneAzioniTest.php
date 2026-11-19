<?php

namespace Tests\Feature;

use App\Filament\Resources\Impostazioni\Pages\ElencoImpostazioni;
use App\Filament\Resources\Interventi\Pages\ElencoInterventi;
use App\Filament\Resources\Modelli\Pages\ElencoModelli;
use App\Models\CarModel;
use App\Models\Intervention;
use App\Models\Setting;
use App\Models\User;
use App\Services\Manutenzione\Backup;
use App\Services\Manutenzione\Import;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

/**
 * L8: le azioni dell'area riservata — impostazioni, modelli e interventi nel sito, backup, import.
 * Ogni modifica passa da equo_import e finisce nel registro con il valore precedente.
 */
class GestioneAzioniTest extends TestCase
{
    private string $cartellaProva;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('gestione');
        $u = new User(['name' => 'Prova', 'email' => 'prova@equo.example', 'password' => 'Prova-Sicura-2026!x']);
        $u->ruolo = User::RUOLO_AMMINISTRATORE;
        $u->app_authentication_secret = 'JBSWY3DPEHPK3PXP';
        $u->save();
        $this->actingAs($u);
        $this->cartellaProva = storage_path('framework/testing/backup-'.uniqid());
        config(['equo.backup.cartella' => $this->cartellaProva]);
    }

    protected function tearDown(): void
    {
        // solo nel database di prova: riporta impostazioni e interruttori ai valori del database vero, toglie account e registro
        $schema = DB::connection('schema');
        foreach (DB::connection('schema')->select('SELECT chiave, valore FROM equo.settings') as $s) {
            $schema->table('settings')->where('chiave', $s->chiave)->update(['valore' => $s->valore]);
        }
        $schema->statement('UPDATE car_models t JOIN equo.car_models o ON o.id = t.id SET t.info_completed = o.info_completed');
        $schema->statement('UPDATE interventions t JOIN equo.interventions o ON o.id = t.id SET t.visibile_nel_form = o.visibile_nel_form');
        DB::connection('import')->table('users')->delete();
        $schema->table('admin_audit_log')->delete();
        File::deleteDirectory($this->cartellaProva);
        parent::tearDown();
    }

    /** @return list<object> */
    private function registro(): array
    {
        return DB::connection('import')->table('admin_audit_log')->orderBy('id')->get()->all();
    }

    // ---------------------------------------------------------------- impostazioni

    public function test_modifica_di_una_soglia_con_registro(): void
    {
        Livewire::test(ElencoImpostazioni::class)
            ->callTableAction('modifica', Setting::findOrFail('soglia_anomalo'), data: ['valore' => '0.3']);

        $this->assertSame('0.30', Setting::findOrFail('soglia_anomalo')->valore);
        $riga = $this->registro()[0];
        $this->assertSame(['impostazione_modificata', 'soglia_anomalo'], [$riga->azione, $riga->oggetto]);
        $this->assertEquals(['prima' => '0.25', 'dopo' => '0.30'], array_intersect_key(json_decode($riga->dettagli, true), ['prima' => 1, 'dopo' => 1]));
    }

    public function test_soglie_incoerenti_o_fuori_limite_non_si_salvano(): void
    {
        // "sopra la media" non può superare "anomalo" (0,25)
        Livewire::test(ElencoImpostazioni::class)
            ->callTableAction('modifica', Setting::findOrFail('soglia_sopra_media'), data: ['valore' => '0.40']);
        // IVA oltre il 50%: rifiutata dal modulo
        Livewire::test(ElencoImpostazioni::class)
            ->callTableAction('modifica', Setting::findOrFail('iva_pct'), data: ['valore' => '99'])
            ->assertHasTableActionErrors(['valore']);

        $this->assertSame('0.10', Setting::findOrFail('soglia_sopra_media')->valore);
        $this->assertSame('22.00', Setting::findOrFail('iva_pct')->valore);
        $this->assertSame([], $this->registro());
    }

    // ---------------------------------------------------------------- modelli e interventi nel sito

    public function test_mettere_e_togliere_un_modello_dal_sito(): void
    {
        $panda = CarModel::where('nome', 'Panda (319)')->firstOrFail();

        Livewire::test(ElencoModelli::class)->callTableAction('accendi', $panda);
        $this->assertTrue(CarModel::findOrFail($panda->id)->info_completed);

        Livewire::test(ElencoModelli::class)->callTableAction('spegni', CarModel::findOrFail($panda->id));
        $this->assertFalse(CarModel::findOrFail($panda->id)->info_completed);

        $this->assertSame(['modello_pronto_per_il_form', 'modello_pronto_per_il_form'], array_column($this->registro(), 'azione'));
    }

    public function test_nascondere_un_intervento_dal_sito(): void
    {
        $batteria = Intervention::where('nome', 'Batteria')->firstOrFail();

        Livewire::test(ElencoInterventi::class)->callTableAction('nascondi', $batteria);

        $this->assertFalse(Intervention::findOrFail($batteria->id)->visibile_nel_form);
        $this->assertSame('intervento_visibile_nel_form', $this->registro()[0]->azione);
    }

    // ---------------------------------------------------------------- backup

    public function test_backup_del_database_di_prova_con_inventario(): void
    {
        if (! File::exists(config('equo.backup.mysqldump'))) {
            $this->markTestSkipped('mysqldump non presente su questo computer');
        }

        $r = app(Backup::class)->esegui();

        $this->assertStringStartsWith('equo_test-', $r['file']);
        $this->assertSame(25, $r['tabelle']);
        $inventario = File::get($this->cartellaProva.'/'.str_replace('.sql', '.inventario.txt', $r['file']));
        $this->assertStringContainsString("tabella\tvariants\t11", $inventario);
        $this->assertSame([], File::glob(storage_path('app/private/mysql-*.cnf')), 'il file temporaneo delle credenziali va cancellato');
        $this->assertCount(1, app(Backup::class)->elenco());
    }

    public function test_pagine_backup_e_import_si_aprono(): void
    {
        $this->get('/gestione/backup')->assertOk()->assertSee('02:30');
        $this->get('/gestione/import')->assertOk()->assertSee('Come funziona');
        $this->get('/gestione/modelli')->assertOk()->assertSee('Panda (319)');
        $this->get('/gestione/interventi')->assertOk()->assertSee('Tagliando completo');
    }

    // ---------------------------------------------------------------- import

    private function fileDiProva(): string
    {
        File::ensureDirectoryExists(Import::cartella());
        $file = Import::cartella().DIRECTORY_SEPARATOR.'prova-'.uniqid().'.xlsx';
        File::put($file, 'contenuto di prova');

        return $file;
    }

    public function test_prova_di_import_sul_database_di_prova_e_senza_salvare(): void
    {
        Process::fake(['*' => Process::result("Modalità: PROVA (nessuna modifica salvata)\nEsito database: OK\n")]);
        $file = $this->fileDiProva();

        $r = app(Import::class)->prova($file);

        $this->assertTrue($r['ok']);
        Process::assertRan(fn ($p) => in_array('--prova', $p->command, true)
            && in_array('--database=equo_test', $p->command, true)
            && collect($p->command)->contains(fn ($a) => str_starts_with($a, '--cnf=')));
        $this->assertSame([], File::glob(storage_path('app/private/mysql-*.cnf')));
        File::delete($file);
    }

    public function test_import_confermato_fa_prima_il_backup(): void
    {
        Process::fake(['*' => Process::result("Modalità: CARICAMENTO\nEsito database: OK\n")]);
        $finto = new class extends Backup
        {
            public int $chiamate = 0;

            public function esegui(): array
            {
                $this->chiamate++;

                return ['file' => 'equo_test-prima-import.sql', 'mb' => 0.1, 'tabelle' => 25, 'procedure' => 0];
            }
        };
        $this->app->instance(Backup::class, $finto);
        $file = $this->fileDiProva();

        $r = app(Import::class)->carica($file);

        $this->assertSame(1, $finto->chiamate);
        $this->assertSame('equo_test-prima-import.sql', $r['backup']);
        Process::assertRan(fn ($p) => ! in_array('--prova', $p->command, true));
        File::delete($file);
    }

    public function test_import_rifiuta_file_fuori_dalla_cartella_o_non_excel(): void
    {
        Process::fake();
        foreach ([base_path('composer.json'), Import::cartella().'/../../../.env'] as $file) {
            try {
                app(Import::class)->prova($file);
                $this->fail("Doveva rifiutare {$file}");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('File non valido', $e->getMessage());
            }
        }
        Process::assertNothingRan();
    }
}
