<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `regions` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE regions (
  id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(40) NOT NULL,
  area  ENUM('nord','centro','sud') NOT NULL COMMENT 'Fascia usata per le assunzioni iniziali sulle tariffe',
  PRIMARY KEY (id),
  UNIQUE KEY uq_regions_nome (nome)
) ENGINE=InnoDB COMMENT='Le 20 regioni italiane'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
