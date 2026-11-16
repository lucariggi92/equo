<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `spare_parts` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE spare_parts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codice       VARCHAR(40) NOT NULL,
  produttore   VARCHAR(60) NOT NULL,
  descrizione  VARCHAR(200) NULL,
  generico     BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Ricambio segnaposto di categoria quando il codice reale non è ancora noto (D5)',
  sottotipo    VARCHAR(40) NULL COMMENT 'Es. disco pieno/ventilato, filtro a cartuccia/avvitamento, abitacolo standard/carboni attivi: affina le stime D5',
  PRIMARY KEY (id),
  UNIQUE KEY uq_spare_parts (produttore, codice)
) ENGINE=InnoDB COMMENT='Anagrafica dei codici: un codice si prezza una volta e vale per tutte le auto compatibili'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
