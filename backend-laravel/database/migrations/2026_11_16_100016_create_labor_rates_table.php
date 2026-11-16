<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `labor_rates` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE labor_rates (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  regione_id            TINYINT UNSIGNED NOT NULL,
  tipo_officina         ENUM('concessionaria','generica','catena') NOT NULL,
  tariffa_oraria_netta  DECIMAL(10,2) NOT NULL,
  origine               ENUM('assunzione','reale') NOT NULL,
  n_campioni            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  data_rilevazione      DATE NOT NULL,
  valido_dal            DATE NOT NULL,
  valido_al             DATE NULL,
  corrente              TINYINT AS (IF(valido_al IS NULL, 1, NULL)) STORED,
  fonte_id              INT UNSIGNED NULL,
  note                  VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_labor_rates_corrente (regione_id, tipo_officina, corrente),
  KEY idx_labor_rates (regione_id, tipo_officina, valido_al),
  CONSTRAINT fk_labor_rates_regione FOREIGN KEY (regione_id) REFERENCES regions (id),
  CONSTRAINT fk_labor_rates_fonte   FOREIGN KEY (fonte_id)   REFERENCES sources (id),
  CONSTRAINT ck_labor_rates_valore CHECK (tariffa_oraria_netta > 0),
  CONSTRAINT ck_labor_rates_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal)
) ENGINE=InnoDB COMMENT='Tariffa oraria netta per regione e tipo officina, con storico'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_rates');
    }
};
