<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `market_benchmarks` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE market_benchmarks (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fonte_id          INT UNSIGNED NOT NULL,
  tipo_dato         ENUM('tariffa_oraria','prezzo_intervento') NOT NULL,
  tipo_officina     ENUM('concessionaria','generica','catena') NULL COMMENT 'NULL = la fonte non lo dice',
  area              ENUM('nazionale','nord','centro','sud') NOT NULL DEFAULT 'nazionale',
  variante_id       SMALLINT UNSIGNED NULL COMMENT 'Solo per prezzo_intervento',
  intervento_id     TINYINT UNSIGNED NULL COMMENT 'Solo per prezzo_intervento',
  valore_min        DECIMAL(10,2) NOT NULL,
  valore_max        DECIMAL(10,2) NOT NULL,
  iva_inclusa       BOOLEAN NOT NULL COMMENT 'Come lo pubblica la fonte; i confronti usano il netto',
  anno_riferimento  SMALLINT UNSIGNED NOT NULL COMMENT 'Anno a cui si riferisce il valore, non la data di lettura',
  data_rilevazione  DATE NOT NULL,
  note              VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_market_benchmarks (tipo_dato, tipo_officina),
  CONSTRAINT fk_market_benchmarks_fonte      FOREIGN KEY (fonte_id)      REFERENCES sources (id),
  CONSTRAINT fk_market_benchmarks_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_market_benchmarks_intervento FOREIGN KEY (intervento_id) REFERENCES interventions (id),
  CONSTRAINT ck_market_benchmarks_valori CHECK (valore_min > 0 AND valore_max >= valore_min),
  CONSTRAINT ck_market_benchmarks_tipo CHECK (
    (tipo_dato = 'tariffa_oraria' AND variante_id IS NULL AND intervento_id IS NULL) OR
    (tipo_dato = 'prezzo_intervento' AND variante_id IS NOT NULL AND intervento_id IS NOT NULL))
) ENGINE=InnoDB COMMENT='Valori di mercato pubblicati (indagini, riviste): tarano tariffe e totali al posto delle fatture (M8)'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('market_benchmarks');
    }
};
