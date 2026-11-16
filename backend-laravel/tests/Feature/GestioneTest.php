<?php

namespace Tests\Feature;

use App\Filament\Pages\Accesso;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * L7: area riservata /gestione — accesso, verifica in due passaggi, ruolo, registro, pagine di sola lettura.
 */
class GestioneTest extends TestCase
{
    private const PASSWORD = 'Prova-Sicura-2026!x';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Filament::setCurrentPanel('gestione');
    }

    protected function tearDown(): void
    {
        // solo nel database di prova: account e righe di registro create dai test
        DB::connection('import')->table('users')->delete();
        DB::connection('schema')->table('admin_audit_log')->delete();
        parent::tearDown();
    }

    private function utente(bool $amministratore = true, bool $dueFattori = true): User
    {
        $u = new User(['name' => 'Prova', 'email' => 'prova@equo.example', 'password' => self::PASSWORD]);
        $u->ruolo = $amministratore ? User::RUOLO_AMMINISTRATORE : null;
        if ($dueFattori) {
            $u->app_authentication_secret = 'JBSWY3DPEHPK3PXP';
            $u->app_authentication_recovery_codes = ['codice-uno', 'codice-due'];
        }
        $u->save();

        return $u;
    }

    /** @return list<string> */
    private function azioniRegistrate(): array
    {
        return DB::connection('import')->table('admin_audit_log')->orderBy('id')->pluck('azione')->all();
    }

    // ---------------------------------------------------------------- senza accesso

    public function test_senza_accesso_si_va_alla_pagina_di_accesso(): void
    {
        $this->get('/gestione')->assertRedirect('/gestione/login');
        $this->get('/gestione/richieste')->assertRedirect('/gestione/login');
    }

    public function test_pagina_di_accesso_protetta_e_senza_risorse_esterne(): void
    {
        $r = $this->get('/gestione/login')->assertOk();

        $csp = $r->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $r->assertHeader('X-Frame-Options', 'DENY');
        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
        $this->assertDoesNotMatchRegularExpression('#(src|href)="https?://(?!localhost)#', $r->getContent());

        $cookie = collect($r->headers->getCookies())->first(fn ($c) => $c->getName() === 'equo_sessione');
        $this->assertNotNull($cookie, 'cookie di sessione equo_sessione');
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('strict', $cookie->getSameSite());
    }

    public function test_non_esiste_registrazione_ne_recupero_via_email(): void
    {
        $this->get('/gestione/register')->assertNotFound();
        $this->get('/gestione/password-reset/request')->assertNotFound();
    }

    // ---------------------------------------------------------------- accesso e registro

    public function test_accesso_riuscito_finisce_nel_registro(): void
    {
        $this->utente();

        Livewire::test(Accesso::class)
            ->fillForm(['email' => 'prova@equo.example', 'password' => self::PASSWORD])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        // con la verifica in due passaggi attiva l'accesso non è completo finché non si inserisce il codice
        $this->assertNotContains('accesso', $this->azioniRegistrate());
    }

    public function test_password_sbagliata_rifiutata_e_registrata(): void
    {
        $this->utente();

        Livewire::test(Accesso::class)
            ->fillForm(['email' => 'prova@equo.example', 'password' => 'sbagliata'])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertSame(['accesso_fallito'], $this->azioniRegistrate());
        $this->assertGuest();
        $riga = DB::connection('import')->table('admin_audit_log')->first();
        $this->assertSame('fallita', $riga->esito);
        $this->assertStringContainsString('prova@equo.example', $riga->dettagli);
    }

    public function test_dopo_5_tentativi_l_accesso_si_blocca_e_si_registra(): void
    {
        $this->utente();
        $pagina = Livewire::test(Accesso::class);
        for ($n = 1; $n <= 6; $n++) {
            $pagina->fillForm(['email' => 'prova@equo.example', 'password' => "sbagliata{$n}"])->call('authenticate');
        }

        $azioni = $this->azioniRegistrate();
        $this->assertSame(5, count(array_keys($azioni, 'accesso_fallito')));
        $this->assertContains('accesso_bloccato', $azioni);
    }

    public function test_account_senza_ruolo_non_entra(): void
    {
        $this->actingAs($this->utente(amministratore: false))->get('/gestione')->assertForbidden();
    }

    public function test_senza_verifica_in_due_passaggi_si_deve_attivare(): void
    {
        $this->actingAs($this->utente(dueFattori: false))->get('/gestione')
            ->assertRedirect('/gestione/multi-factor-authentication/set-up');
    }

    // ---------------------------------------------------------------- pagine di sola lettura

    public function test_le_pagine_si_aprono_e_non_permettono_modifiche(): void
    {
        $this->actingAs($this->utente());

        $this->get('/gestione')->assertOk()->assertSee('EQUO · gestione');
        // i riquadri si caricano dopo la pagina: si provano da soli
        Livewire::test(\App\Filament\Widgets\StatoDati::class)->assertSee('Stato dei dati')
            ->assertSee('Prezzi verificati su 2 negozi')->assertSee('2 su 73')->assertSee('Modelli pronti per il form');
        $this->get('/gestione/richieste')->assertOk()->assertSee('Richieste ricevute');
        $this->get('/gestione/registro')->assertOk();
        $this->get('/gestione/impostazioni')->assertOk()->assertSee('iva_pct');

        foreach (['richieste', 'registro', 'impostazioni'] as $pagina) {
            $this->get("/gestione/{$pagina}/create")->assertNotFound();
            $this->get("/gestione/{$pagina}/1/edit")->assertNotFound();
        }
    }

    public function test_area_chiusa_agli_indirizzi_non_ammessi(): void
    {
        config(['equo.gestione_indirizzi_ammessi' => ['10.1.2.3']]);
        $this->get('/gestione/login')->assertNotFound();

        config(['equo.gestione_indirizzi_ammessi' => ['127.0.0.1']]);
        $this->get('/gestione/login')->assertOk();
    }

    // ---------------------------------------------------------------- comandi sul server

    public function test_crea_amministratore_rifiuta_password_deboli(): void
    {
        $this->artisan('equo:crea-amministratore')
            ->expectsQuestion('Nome', 'Luca')
            ->expectsQuestion('Email', 'luca@equo.example')
            ->expectsQuestion('Password (non compare mentre la scrivi)', 'corta1!')
            ->expectsQuestion('Ripeti la password', 'corta1!')
            ->assertFailed();

        $this->assertSame(0, DB::connection('import')->table('users')->count());
    }

    public function test_crea_amministratore_e_recupera_accesso(): void
    {
        $this->artisan('equo:crea-amministratore')
            ->expectsQuestion('Nome', 'Luca')
            ->expectsQuestion('Email', 'luca@equo.example')
            ->expectsQuestion('Password (non compare mentre la scrivi)', self::PASSWORD)
            ->expectsQuestion('Ripeti la password', self::PASSWORD)
            ->assertSuccessful();

        $u = User::where('email', 'luca@equo.example')->firstOrFail();
        $this->assertSame(User::RUOLO_AMMINISTRATORE, $u->ruolo);
        $this->assertNotSame(self::PASSWORD, $u->getAuthPassword());

        $u->app_authentication_secret = 'JBSWY3DPEHPK3PXP';
        $u->save();
        $this->artisan('equo:recupera-accesso', ['email' => 'luca@equo.example', '--azzera-due-fattori' => true])
            ->expectsQuestion('Nuova password (non compare mentre la scrivi)', 'Nuova-Password-2026!')
            ->expectsQuestion('Ripeti la nuova password', 'Nuova-Password-2026!')
            ->assertSuccessful();

        $u->refresh();
        $this->assertNull($u->app_authentication_secret);
        $this->assertSame(['account_creato', 'accesso_recuperato'], $this->azioniRegistrate());
    }
}
