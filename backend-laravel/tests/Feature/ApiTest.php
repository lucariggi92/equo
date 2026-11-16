<?php

namespace Tests\Feature;

use App\Models\CarModel;
use App\Models\Intervention;
use App\Models\Region;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * L5: le quattro rotte dell'API per il sito React — risposte, controlli sui dati in ingresso, limiti di traffico.
 */
class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // nel database di prova nessun modello è ancora marcato "completo": per i test si mostrano tutti
        config(['equo.solo_modelli_completi' => false]);
        RateLimiter::clear('letture|127.0.0.1');
        RateLimiter::clear('verifica-minuto|127.0.0.1');
        RateLimiter::clear('verifica-giorno|127.0.0.1');
    }

    protected function tearDown(): void
    {
        // le richieste salvate dai test si tolgono (solo nel database di prova)
        DB::connection('import')->table('quote_requests')->delete();
        parent::tearDown();
    }

    private function id(string $modello): int
    {
        return Variant::whereHas('modello', fn ($q) => $q->where('nome', $modello))->value('id');
    }

    /** @return array<string, mixed> */
    private function verificaPanda(array $cambia = []): array
    {
        return array_merge([
            'variante_id' => $this->id('Panda (319)'),
            'intervento_id' => Intervention::where('nome', 'Tagliando completo')->value('id'),
            'qualita' => 'equivalente',
            'regione_id' => Region::where('nome', 'Lombardia')->value('id'),
            'tipo_officina' => 'generica',
            'importo_preventivo' => 230,
            'iva_inclusa' => true,
        ], $cambia);
    }

    private function richiesteSalvate(): int
    {
        return DB::connection('import')->table('quote_requests')->count();
    }

    // ---------------------------------------------------------------- /api/opzioni

    public function test_opzioni_restituisce_gli_elenchi_del_form(): void
    {
        $r = $this->getJson('/api/opzioni')->assertOk()
            ->assertJsonCount(20, 'regioni')
            ->assertJsonCount(10, 'interventi')
            ->assertJsonCount(3, 'qualita')
            ->assertJsonCount(3, 'tipi_officina');

        $fiat = collect($r->json('marche'))->firstWhere('nome', 'Fiat');
        $this->assertContains('Panda (319)', array_column($fiat['modelli'], 'nome'));
        $this->assertSame(['valore', 'etichetta'], array_keys($r->json('qualita.0')));
    }

    public function test_opzioni_mostra_solo_i_modelli_completi_quando_richiesto(): void
    {
        config(['equo.solo_modelli_completi' => true]);
        // oggi nessuno dei 10 modelli è marcato completo: il form resta vuoto finché il PM non li segna
        $this->assertSame(0, CarModel::where('info_completed', true)->count());
        $this->getJson('/api/opzioni')->assertOk()->assertJsonCount(0, 'marche');
    }

    // ---------------------------------------------------------------- /api/varianti

    public function test_varianti_di_un_modello(): void
    {
        $modello = CarModel::where('nome', 'Panda (319)')->value('id');

        $this->getJson("/api/varianti?modello_id={$modello}")->assertOk()
            ->assertJsonCount(1, 'varianti')
            ->assertJsonPath('varianti.0.etichetta', '1.2 benzina 69 CV')
            ->assertJsonPath('varianti.0.cambio', 'manuale')
            ->assertJsonPath('varianti.0.interventi_non_previsti', []);
    }

    public function test_varianti_filtrate_per_anno(): void
    {
        $modello = CarModel::where('nome', '500 (312)')->value('id');

        $this->getJson("/api/varianti?modello_id={$modello}")->assertJsonCount(2, 'varianti');
        $this->getJson("/api/varianti?modello_id={$modello}&anno=2010")->assertJsonCount(1, 'varianti')
            ->assertJsonPath('varianti.0.etichetta', '1.2 benzina 69 CV (2007-2015)');
        $this->getJson("/api/varianti?modello_id={$modello}&anno=2018")->assertJsonCount(1, 'varianti')
            ->assertJsonPath('varianti.0.etichetta', '1.2 benzina 69 CV (2015-2020)');
    }

    public function test_la_yaris_ibrida_non_propone_la_frizione(): void
    {
        $modello = CarModel::where('nome', 'Yaris III (XP13)')->value('id');
        $frizione = Intervention::where('nome', 'Frizione')->value('id');

        $this->getJson("/api/varianti?modello_id={$modello}")->assertOk()
            ->assertJsonPath('varianti.0.cambio', 'automatico')
            ->assertJsonPath('varianti.0.interventi_non_previsti', [$frizione]);
    }

    public function test_varianti_con_dati_sbagliati(): void
    {
        $this->getJson('/api/varianti')->assertStatus(422)->assertJsonValidationErrors('modello_id');
        $this->getJson('/api/varianti?modello_id=99999')->assertStatus(422)->assertJsonValidationErrors('modello_id');
        $this->getJson('/api/varianti?modello_id=abc')->assertStatus(422);
        $this->getJson('/api/varianti?modello_id=1&anno=1900')->assertStatus(422)->assertJsonValidationErrors('anno');
    }

    // ---------------------------------------------------------------- /api/verifica

    public function test_verifica_tagliando_panda_e_salva_la_richiesta(): void
    {
        $this->postJson('/api/verifica', $this->verificaPanda())->assertOk()
            ->assertJsonPath('riepilogo.stima_iva_inclusa', '242.84')
            ->assertJsonPath('riepilogo.stima_da', '218.43')
            ->assertJsonPath('riepilogo.stima_a', '259.79')
            ->assertJsonPath('riepilogo.completo', true)
            ->assertJsonPath('verdetto.scostamento_pct', '-5.3')
            ->assertJsonPath('verdetto.esito', 'equo')
            ->assertJsonCount(6, 'dettaglio');

        $salvata = DB::connection('import')->table('quote_requests')->first();
        $this->assertSame(['230.00', '242.84', 'equo'], [$salvata->importo_preventivo, $salvata->stima_calcolata, $salvata->esito]);
        $this->assertNull($salvata->altro_intervento);
    }

    public function test_preventivo_senza_iva_si_porta_al_lordo(): void
    {
        // 200 € + IVA 22% = 244 €: +0,5% sulla stima, equo
        $this->postJson('/api/verifica', $this->verificaPanda(['importo_preventivo' => 200, 'iva_inclusa' => false]))->assertOk()
            ->assertJsonPath('preventivo_iva_inclusa', '244.00')
            ->assertJsonPath('verdetto.scostamento_pct', '0.5')
            ->assertJsonPath('verdetto.esito', 'equo');
    }

    public function test_intervento_non_in_elenco_si_registra_senza_stima(): void
    {
        $dati = $this->verificaPanda(['intervento_id' => null, 'altro_intervento' => 'Sostituzione ammortizzatori posteriori']);

        $this->postJson('/api/verifica', $dati)->assertOk()->assertJsonPath('esito', 'non_calcolabile');
        $salvata = DB::connection('import')->table('quote_requests')->first();
        $this->assertSame(['Sostituzione ammortizzatori posteriori', 'non_calcolabile'], [$salvata->altro_intervento, $salvata->esito]);
    }

    public function test_verifica_con_dati_sbagliati_viene_rifiutata_e_non_salva_niente(): void
    {
        $casi = [
            'variante_id' => ['variante_id' => 99999],
            'qualita' => ['qualita' => 'usato'],
            'tipo_officina' => ['tipo_officina' => 'amico'],
            'regione_id' => ['regione_id' => 0],
            'iva_inclusa' => ['iva_inclusa' => 'forse'],
        ];
        foreach ($casi as $campo => $cambia) {
            $this->postJson('/api/verifica', $this->verificaPanda($cambia))->assertStatus(422)->assertJsonValidationErrors($campo);
        }
        foreach ([-10, 0, 20001, 'abc', 12.345] as $importo) {
            $this->postJson('/api/verifica', $this->verificaPanda(['importo_preventivo' => $importo]))
                ->assertStatus(422)->assertJsonValidationErrors('importo_preventivo');
        }
        $this->postJson('/api/verifica', [])->assertStatus(422)
            ->assertJsonValidationErrors(['variante_id', 'intervento_id', 'qualita', 'regione_id', 'tipo_officina', 'importo_preventivo', 'iva_inclusa']);

        $this->assertSame(0, $this->richiesteSalvate());
    }

    public function test_intervento_non_previsto_per_quella_auto(): void
    {
        $dati = $this->verificaPanda([
            'variante_id' => $this->id('Yaris III (XP13)'),
            'intervento_id' => Intervention::where('nome', 'Frizione')->value('id'),
        ]);

        $this->postJson('/api/verifica', $dati)->assertStatus(422)->assertJsonValidationErrors('intervento_id');
    }

    public function test_anno_fuori_produzione(): void
    {
        $dati = $this->verificaPanda(['variante_id' => $this->id('500 (312)'), 'anno_immatricolazione' => 2022]);
        // la prima 500 di elenco è quella 2007-2015 o 2015-2020: in entrambi i casi il 2022 è fuori
        $this->postJson('/api/verifica', $dati)->assertStatus(422)->assertJsonValidationErrors('anno_immatricolazione');
    }

    public function test_campi_in_piu_ignorati(): void
    {
        $this->postJson('/api/verifica', $this->verificaPanda(['stima_calcolata' => 1, 'esito' => 'anomalo', 'id' => 5]))
            ->assertOk()->assertJsonPath('verdetto.esito', 'equo');
        $this->assertSame('equo', DB::connection('import')->table('quote_requests')->value('esito'));
    }

    // ---------------------------------------------------------------- /api/salute e metodi

    public function test_salute_dice_solo_ok(): void
    {
        $r = $this->getJson('/api/salute')->assertOk()->assertExactJson(['stato' => 'ok']);
        $this->assertStringNotContainsStringIgnoringCase('laravel', $r->getContent());
    }

    public function test_metodi_non_previsti_rifiutati(): void
    {
        $this->putJson('/api/verifica', $this->verificaPanda())->assertStatus(405);
        $this->deleteJson('/api/verifica')->assertStatus(405);
        $this->postJson('/api/opzioni')->assertStatus(405);
        $this->getJson('/api/verifica')->assertStatus(405);
        $this->getJson('/api/non-esiste')->assertStatus(404);
        $this->getJson('/up')->assertStatus(404);
    }

    // ---------------------------------------------------------------- limiti di traffico

    public function test_limite_di_20_verifiche_al_minuto(): void
    {
        for ($n = 1; $n <= 20; $n++) {
            $this->postJson('/api/verifica', $this->verificaPanda())->assertOk();
        }
        $this->postJson('/api/verifica', $this->verificaPanda())->assertStatus(429);
        $this->assertSame(20, $this->richiesteSalvate());
    }

    public function test_limite_di_120_letture_al_minuto(): void
    {
        for ($n = 1; $n <= 120; $n++) {
            $this->getJson('/api/salute')->assertOk();
        }
        $this->getJson('/api/salute')->assertStatus(429);
    }
}
