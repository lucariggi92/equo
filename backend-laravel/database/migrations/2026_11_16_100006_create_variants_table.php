<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `variants` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE variants (
  id                SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  modello_id        SMALLINT UNSIGNED NOT NULL,
  etichetta         VARCHAR(80) NOT NULL COMMENT 'Testo mostrato all''utente, es. "1.2 benzina 69 CV 2012-2018"',
  codice_motore     VARCHAR(30) NULL,
  cilindrata        SMALLINT UNSIGNED NULL COMMENT 'cm3',
  potenza_kw        SMALLINT UNSIGNED NULL,
  alimentazione     ENUM('benzina','diesel','gpl','metano','ibrida','elettrica') NOT NULL,
  distribuzione     ENUM('cinghia','catena') NULL,
  cambio            ENUM('manuale','robotizzato','automatico') NULL COMMENT 'manuale e robotizzato hanno la frizione; automatico (convertitore, e-CVT, per ora anche doppia frizione) no',
  olio_specifica    VARCHAR(40) NULL COMMENT 'es. 5W-30 C3; i litri stanno in intervention_components',
  anno_produzione_inizio  SMALLINT UNSIGNED NOT NULL COMMENT 'Primo anno in cui questa motorizzazione è stata prodotta',
  anno_produzione_fine    SMALLINT UNSIGNED NULL COMMENT 'Ultimo anno di produzione; NULL = ancora in produzione',
  PRIMARY KEY (id),
  UNIQUE KEY uq_variants (modello_id, etichetta),
  CONSTRAINT fk_variants_modello FOREIGN KEY (modello_id) REFERENCES car_models (id),
  CONSTRAINT ck_variants_anni CHECK (anno_produzione_fine IS NULL OR anno_produzione_fine >= anno_produzione_inizio),
  CONSTRAINT ck_variants_anno_inizio CHECK (anno_produzione_inizio BETWEEN 1980 AND 2100)
) ENGINE=InnoDB COMMENT='La chiave reale per i ricambi: motore e generazione. Il form filtra le varianti con l''anno di immatricolazione dell''utente'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
