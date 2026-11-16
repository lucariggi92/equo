<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `calibrations` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('calibrations');
    }
};
