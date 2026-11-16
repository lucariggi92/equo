<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `settings` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE settings (
  chiave       VARCHAR(50)  NOT NULL,
  valore       VARCHAR(100) NOT NULL,
  descrizione  VARCHAR(255) NULL,
  PRIMARY KEY (chiave)
) ENGINE=InnoDB COMMENT='Parametri globali: IVA, soglie del verdetto, minimi'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
