<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\LaborHour;
use App\Models\LaborRate;
use App\Models\Markup;
use App\Models\PartPrice;
use App\Models\Region;
use App\Models\Setting;
use App\Models\SparePart;
use App\Models\Variant;
use Tests\TestCase;

/**
 * L3: i modelli leggono i dati veri (copiati in equo_test) e le relazioni principali funzionano.
 */
class ModelliTest extends TestCase
{
    private function panda(): Variant
    {
        return Variant::whereHas('modello', fn ($q) => $q->where('nome', 'Panda (319)'))->firstOrFail();
    }

    public function test_le_tabelle_principali_si_leggono(): void
    {
        $this->assertSame(11, Variant::count());
        $this->assertSame(20, Region::count());
        $this->assertSame(10, Intervention::count());
        $this->assertSame('22.00', Setting::findOrFail('iva_pct')->valore);
    }

    public function test_la_panda_ha_marca_modello_e_tipo_di_cambio(): void
    {
        $panda = $this->panda();

        $this->assertSame('Fiat', $panda->modello->marca->nome);
        $this->assertSame('1.2 benzina 69 CV', $panda->etichetta);
        $this->assertSame('manuale', $panda->cambio);
    }

    public function test_il_tagliando_della_panda_ha_la_sua_composizione(): void
    {
        $tagliando = Intervention::where('nome', 'Tagliando completo')->firstOrFail();
        $componenti = $this->panda()->composizione()
            ->where('intervento_id', $tagliando->id)
            ->with('componente')
            ->get()
            ->pluck('componente.descrizione');

        $this->assertContains('Olio motore', $componenti);
        $this->assertContains('Filtro olio', $componenti);
        $this->assertCount(5, $componenti);
    }

    public function test_prezzo_corrente_e_campioni_del_filtro_olio_verificato(): void
    {
        $filtro = SparePart::where('produttore', 'MANN-FILTER')->where('codice', 'W 610/3')->firstOrFail();
        $prezzo = $filtro->prezzoCorrente;

        $this->assertSame('rilevato', $prezzo->metodo);
        $this->assertFalse($prezzo->da_avvalorare);
        $this->assertSame('8.0615', $prezzo->prezzo_netto);
        $this->assertSame(2, $prezzo->campioni()->where('scartato', false)->count());
    }

    public function test_valori_correnti_di_prezzi_tariffe_e_ricarichi(): void
    {
        $this->assertSame(73, PartPrice::correnti()->count());
        $this->assertSame(60, LaborRate::correnti()->count());
        $this->assertSame(6, Markup::correnti()->count());
    }

    public function test_ore_del_tagliando_panda_dal_tempario(): void
    {
        $ore = LaborHour::where('variante_id', $this->panda()->id)
            ->whereHas('intervento', fn ($q) => $q->where('nome', 'Tagliando completo'))
            ->firstOrFail();

        $this->assertSame('1.41', $ore->ore);
        $this->assertSame('tempario', $ore->origine);
    }
}
