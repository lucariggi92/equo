<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `markups` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE markups (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_officina  ENUM('concessionaria','generica','catena') NOT NULL,
  tipo_fonte     ENUM('ufficiale','media') NOT NULL COMMENT 'Tipo di prezzo a cui si applica',
  fattore        DECIMAL(6,4) NOT NULL COMMENT 'Additivo: 0.18 = +18%; può essere 0 o negativo',
  origine        ENUM('assunzione','taratura') NOT NULL,
  n_campioni     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  valido_dal     DATE NOT NULL,
  valido_al      DATE NULL,
  corrente       TINYINT AS (IF(valido_al IS NULL, 1, NULL)) STORED,
  note           VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_markups_corrente (tipo_officina, tipo_fonte, corrente),
  KEY idx_markups (tipo_officina, tipo_fonte, valido_al),
  CONSTRAINT ck_markups_fattore CHECK (fattore BETWEEN -0.5000 AND 2.0000),
  CONSTRAINT ck_markups_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal)
) ENGINE=InnoDB COMMENT='Ricarico sui ricambi per tipo officina e tipo di prezzo (niente regione)'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('markups');
    }
};
