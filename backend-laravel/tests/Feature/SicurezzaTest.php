<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/**
 * L6: i controlli che ogni richiesta attraversa (decisione D8, sezione 4 del documento di struttura).
 */
class SicurezzaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['equo.solo_modelli_completi' => false, 'cors.allowed_origins' => ['https://www.equo.example']]);
        RateLimiter::clear('letture|127.0.0.1');
        RateLimiter::clear('verifica-minuto|127.0.0.1');
    }

    // ---------------------------------------------------------------- intestazioni

    public function test_intestazioni_di_sicurezza_su_ogni_risposta_dell_api(): void
    {
        $r = $this->getJson('/api/salute')->assertOk();

        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('X-Frame-Options', 'DENY');
        $r->assertHeader('Referrer-Policy', 'no-referrer');
        $r->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $this->assertStringContainsString('camera=()', $r->headers->get('Permissions-Policy'));
        $this->assertStringContainsString("default-src 'none'", $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'none'", $r->headers->get('Content-Security-Policy'));
        $r->assertHeaderMissing('X-Powered-By');
    }

    public function test_intestazioni_anche_sulle_risposte_di_errore(): void
    {
        $r = $this->getJson('/api/non-esiste')->assertNotFound();
        $r->assertHeader('X-Frame-Options', 'DENY');
        $this->assertNotNull($r->headers->get('X-Request-Id'));
    }

    public function test_ogni_richiesta_ha_un_codice_diverso_generato_dal_server(): void
    {
        $a = $this->getJson('/api/salute')->headers->get('X-Request-Id');
        $b = $this->withHeaders(['X-Request-Id' => 'scelto-da-fuori'])->getJson('/api/salute')->headers->get('X-Request-Id');

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $a);
        $this->assertNotSame($a, $b);
        $this->assertNotSame('scelto-da-fuori', $b);
    }

    // ---------------------------------------------------------------- HTTPS

    public function test_hsts_sulle_risposte_cifrate(): void
    {
        $this->getJson('https://localhost/api/salute')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $this->getJson('http://localhost/api/salute')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_con_https_obbligatorio_niente_in_chiaro(): void
    {
        config(['equo.forza_https' => true]);

        $this->get('http://localhost/api/salute')->assertStatus(301)->assertRedirect('https://localhost/api/salute');
        $this->postJson('http://localhost/api/verifica', [])->assertStatus(403);
        $this->getJson('https://localhost/api/salute')->assertOk();
    }

    // ---------------------------------------------------------------- CORS: solo il sito React

    public function test_il_browser_accetta_le_risposte_solo_dal_sito_react(): void
    {
        $this->withHeaders(['Origin' => 'https://www.equo.example'])->getJson('/api/salute')
            ->assertHeader('Access-Control-Allow-Origin', 'https://www.equo.example');
        // a un altro sito non si dà mai il permesso: con un solo indirizzo ammesso la risposta indica quello
        // (il browser vede che non coincide e blocca), con più indirizzi l'intestazione non c'è proprio
        $altro = $this->withHeaders(['Origin' => 'https://sito-qualunque.example'])->getJson('/api/salute');
        $this->assertNotSame('https://sito-qualunque.example', $altro->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('*', $altro->headers->get('Access-Control-Allow-Origin'));

        config(['cors.allowed_origins' => ['https://www.equo.example', 'https://equo.example']]);
        $this->app->forgetInstance(\Fruitcake\Cors\CorsService::class);
        $this->app->forgetInstance(\Illuminate\Http\Middleware\HandleCors::class);
        $this->withHeaders(['Origin' => 'https://sito-qualunque.example'])->getJson('/api/salute')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_controllo_preventivo_del_browser(): void
    {
        $r = $this->withHeaders([
            'Origin' => 'https://www.equo.example',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
        ])->options('/api/verifica');

        $r->assertNoContent();
        $r->assertHeader('Access-Control-Allow-Origin', 'https://www.equo.example');
        $this->assertSame('GET, POST', $r->headers->get('Access-Control-Allow-Methods'));
    }

    // ---------------------------------------------------------------- metodi, dimensione, formato

    public function test_modifica_e_cancellazione_rifiutate_anche_se_travestite(): void
    {
        $this->putJson('/api/verifica', [])->assertStatus(405);
        $this->patchJson('/api/opzioni')->assertStatus(405);
        $this->deleteJson('/api/salute')->assertStatus(405);
        $this->postJson('/api/salute')->assertStatus(405);
        $this->withHeaders(['X-HTTP-Method-Override' => 'DELETE'])->postJson('/api/verifica', [])->assertStatus(400);
        $this->postJson('/api/verifica', ['_method' => 'PUT'])->assertStatus(400);
        $this->postJson('/api/verifica?_method=DELETE', [])->assertStatus(400);
    }

    public function test_richiesta_troppo_grande_rifiutata(): void
    {
        $this->postJson('/api/verifica', ['altro_intervento' => str_repeat('x', 20000)])->assertStatus(413);
    }

    public function test_la_verifica_accetta_solo_json(): void
    {
        $this->post('/api/verifica', ['variante_id' => 1])->assertStatus(415)
            ->assertJson(['message' => 'La richiesta deve essere in formato JSON.']);
    }

    // ---------------------------------------------------------------- niente informazioni sul software

    public function test_nessuna_pagina_web_pubblica(): void
    {
        $r = $this->get('/')->assertNotFound();
        $this->assertStringNotContainsStringIgnoringCase('laravel', $r->getContent());
        $this->get('/up')->assertNotFound();
    }

    public function test_gli_errori_non_mostrano_dettagli_interni(): void
    {
        config(['app.debug' => false]);
        Route::middleware('api')->get('/api/prova-errore', fn () => throw new RuntimeException('dettaglio segreto del server'));

        $r = $this->getJson('/api/prova-errore')->assertStatus(500);
        $this->assertSame(['message' => 'Server Error'], $r->json());
        $this->assertStringNotContainsString('segreto', $r->getContent());
        $this->assertStringNotContainsString('.php', $r->getContent());
    }
}
