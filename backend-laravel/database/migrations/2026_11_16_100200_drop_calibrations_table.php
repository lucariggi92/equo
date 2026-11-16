<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Toglie la tabella delle fatture reali (decisione del PM, 16/11/2026).
 * Le fatture non arriveranno: con la decisione D7 la taratura usa i valori pubblicati (market_benchmarks)
 * e, dopo il lancio, i preventivi degli utenti (quote_requests). La tabella era vuota.
 * Con lei va anche l'impostazione che serviva solo a lei (min_campioni_taratura).
 * down() rimette tutto com'era.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('calibrations');
        DB::table('settings')->where('chiave', 'min_campioni_taratura')->delete();
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE calibrations (
  id                        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fonte_id                  INT UNSIGNED NOT NULL COMMENT 'La fattura o il preventivo reale',
  ricambio_id               INT UNSIGNED NOT NULL,
  tipo_officina             ENUM('concessionaria','generica','catena') NOT NULL,
  regione_id                TINYINT UNSIGNED NOT NULL,
  prezzo_fattura_netto      DECIMAL(10,4) NOT NULL,
  prezzo_riferimento_netto  DECIMAL(10,4) NOT NULL COMMENT 'Prezzo corrente dello stesso codice in part_prices',
  tipo_fonte_riferimento    ENUM('ufficiale','media') NOT NULL,
  rapporto                  DECIMAL(10,4) AS (prezzo_fattura_netto / prezzo_riferimento_netto) STORED,
  data_fattura              DATE NOT NULL,
  PRIMARY KEY (id),
  KEY idx_calibrations (tipo_officina, tipo_fonte_riferimento),
  CONSTRAINT fk_calibrations_fonte    FOREIGN KEY (fonte_id)    REFERENCES sources (id),
  CONSTRAINT fk_calibrations_ricambio FOREIGN KEY (ricambio_id) REFERENCES spare_parts (id),
  CONSTRAINT fk_calibrations_regione  FOREIGN KEY (regione_id)  REFERENCES regions (id),
  CONSTRAINT ck_calibrations_prezzi CHECK (prezzo_fattura_netto > 0 AND prezzo_riferimento_netto > 0)
) ENGINE=InnoDB COMMENT='Pezzi letti su fatture reali: la mediana di rapporto - 1 dà il fattore in markups'
SQL);
        DB::table('settings')->insertOrIgnore([
            'chiave' => 'min_campioni_taratura',
            'valore' => '5',
            'descrizione' => 'Campioni minimi per pubblicare un ricarico tarato',
        ]);
    }
};
