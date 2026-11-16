<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `labor_hours` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE labor_hours (
  variante_id    SMALLINT UNSIGNED NOT NULL,
  intervento_id  TINYINT UNSIGNED NOT NULL,
  ore            DECIMAL(6,2) NOT NULL,
  origine        ENUM('assunzione','tempario','reale') NOT NULL COMMENT 'tempario: tempo pubblicato per la versione o il modello; reale: fattura od officina',
  fonte_id       INT UNSIGNED NULL,
  note           VARCHAR(255) NULL,
  PRIMARY KEY (variante_id, intervento_id),
  KEY idx_labor_hours_intervento (intervento_id),
  CONSTRAINT fk_labor_hours_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_labor_hours_intervento FOREIGN KEY (intervento_id) REFERENCES interventions (id),
  CONSTRAINT fk_labor_hours_fonte      FOREIGN KEY (fonte_id)      REFERENCES sources (id),
  CONSTRAINT ck_labor_hours CHECK (ore > 0)
) ENGINE=InnoDB COMMENT='Tempario: ore per intervento (anche pezzi singoli); il minimo è in settings'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_hours');
    }
};
