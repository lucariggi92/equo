<?php

namespace Tests\Feature;

use App\Exceptions\ModificaNonPermessa;
use App\Models\AdminAuditLog;
use App\Models\Brand;
use App\Models\PartPrice;
use App\Models\QuoteRequest;
use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use App\Models\Variant;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * L3: il catalogo è in sola lettura nel codice (strato 3) e nel database (strato 4);
 * richieste degli utenti e registro delle azioni si possono solo aggiungere.
 */
class ProtezioneDatiTest extends TestCase
{
    private function rifiutatoDalCodice(Closure $azione): void
    {
        try {
            $azione();
            $this->fail('La modifica doveva essere rifiutata dal codice.');
        } catch (ModificaNonPermessa $e) {
            $this->assertStringContainsString('non permessa', $e->getMessage());
        }
    }

    private function rifiutatoDaMysql(Closure $azione): void
    {
        try {
            $azione();
            $this->fail('La modifica doveva essere rifiutata da MySQL.');
        } catch (QueryException $e) {
            // 1142 = comando non permesso a questo utente MySQL
            $this->assertStringContainsString('1142', $e->getMessage());
        }
    }

    public function test_un_prezzo_non_si_modifica_ne_si_cancella_dai_modelli(): void
    {
        $prezzo = PartPrice::correnti()->firstOrFail();
        $originale = $prezzo->prezzo_netto;

        $this->rifiutatoDalCodice(function () use ($prezzo) {
            $prezzo->prezzo_netto = '1.0000';
            $prezzo->save();
        });
        $this->rifiutatoDalCodice(fn () => $prezzo->saveQuietly());
        $this->rifiutatoDalCodice(fn () => $prezzo->delete());
        $this->rifiutatoDalCodice(fn () => $prezzo->deleteQuietly());

        $this->assertSame($originale, PartPrice::whereKey($prezzo->id)->value('prezzo_netto'));
    }

    public function test_nessuna_riga_nuova_nel_catalogo(): void
    {
        $this->rifiutatoDalCodice(fn () => (new Brand)->forceFill(['nome' => 'Prova'])->save());
        $this->rifiutatoDalCodice(fn () => Brand::insert(['nome' => 'Prova']));
        $this->assertSame(0, Brand::where('nome', 'Prova')->count());
    }

    public function test_scritture_di_massa_rifiutate(): void
    {
        $this->rifiutatoDalCodice(fn () => PartPrice::query()->update(['prezzo_netto' => 1]));
        $this->rifiutatoDalCodice(fn () => Setting::where('chiave', 'soglia_anomalo')->update(['valore' => '0.50']));
        $this->rifiutatoDalCodice(fn () => Region::query()->delete());
        $this->rifiutatoDalCodice(fn () => Variant::query()->truncate());
        $this->rifiutatoDalCodice(fn () => PartPrice::query()->increment('n_campioni'));
        $this->rifiutatoDalCodice(fn () => Brand::upsert([['id' => 1, 'nome' => 'X']], ['id']));

        $this->assertSame('0.25', Setting::findOrFail('soglia_anomalo')->valore);
    }

    public function test_mysql_rifiuta_anche_se_si_passa_sotto_i_modelli(): void
    {
        $this->rifiutatoDaMysql(fn () => DB::table('part_prices')->update(['prezzo_netto' => 1]));
        $this->rifiutatoDaMysql(fn () => DB::table('brands')->insert(['nome' => 'Prova']));
        $this->rifiutatoDaMysql(fn () => DB::table('settings')->delete());
        $this->rifiutatoDaMysql(fn () => DB::table('users')->count());
        $this->rifiutatoDaMysql(fn () => DB::table('admin_audit_log')->count());
    }

    public function test_le_richieste_degli_utenti_si_possono_solo_aggiungere(): void
    {
        $db = DB::connection('mysql');
        $db->beginTransaction();
        try {
            $richiesta = QuoteRequest::create([
                'variante_id' => Variant::value('id'),
                'intervento_id' => DB::table('interventions')->where('nome', 'Tagliando completo')->value('id'),
                'qualita' => 'equivalente',
                'regione_id' => Region::where('nome', 'Lombardia')->value('id'),
                'tipo_officina' => 'generica',
                'importo_preventivo' => 230,
                'iva_inclusa' => true,
            ]);
            $this->assertTrue($richiesta->exists);

            $this->rifiutatoDalCodice(function () use ($richiesta) {
                $richiesta->importo_preventivo = 1;
                $richiesta->save();
            });
            $this->rifiutatoDalCodice(fn () => $richiesta->delete());
            $this->rifiutatoDalCodice(fn () => QuoteRequest::query()->update(['esito' => 'equo']));
            $this->rifiutatoDaMysql(fn () => DB::table('quote_requests')->update(['esito' => 'equo']));
        } finally {
            $db->rollBack();
        }
    }

    public function test_il_registro_delle_azioni_si_puo_solo_aggiungere(): void
    {
        $db = DB::connection('import');
        $db->beginTransaction();
        try {
            $riga = AdminAuditLog::create(['azione' => 'prova_test', 'esito' => 'riuscita', 'dettagli' => ['nota' => 'test L3']]);
            $this->assertTrue($riga->exists);

            $this->rifiutatoDalCodice(function () use ($riga) {
                $riga->esito = 'fallita';
                $riga->save();
            });
            $this->rifiutatoDalCodice(fn () => $riga->delete());
            $this->rifiutatoDaMysql(fn () => $db->table('admin_audit_log')->update(['esito' => 'fallita']));
            $this->rifiutatoDaMysql(fn () => $db->table('admin_audit_log')->delete());
        } finally {
            $db->rollBack();
        }
    }

    public function test_gli_account_si_leggono_solo_dal_collegamento_dell_area_riservata(): void
    {
        $this->assertSame('import', (new User)->getConnectionName());
        $this->assertIsInt(User::count());
    }
}
