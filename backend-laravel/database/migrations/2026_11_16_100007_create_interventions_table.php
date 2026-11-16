<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `interventions` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE interventions (
  id           TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome         VARCHAR(80) NOT NULL,
  descrizione  VARCHAR(255) NULL,
  tipo         ENUM('pacchetto','singolo') NOT NULL,
  visibile_nel_form  BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'FALSE = non compare nel menu del form (es. intervento sospeso)',
  PRIMARY KEY (id),
  UNIQUE KEY uq_interventions_nome (nome)
) ENGINE=InnoDB COMMENT='Elenco interventi del form: pacchetti (tagliando) e pezzi singoli, stesso calcolo'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
