<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `components` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE components (
  id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  descrizione  VARCHAR(80) NOT NULL,
  categoria    ENUM('filtri','olio','accensione','freni','distribuzione','frizione','batteria') NOT NULL,
  unita        ENUM('pezzo','set','kit','litro') NOT NULL COMMENT 'Unità a cui si riferiscono quantità e prezzo',
  PRIMARY KEY (id),
  UNIQUE KEY uq_components_descrizione (descrizione)
) ENGINE=InnoDB COMMENT='Slot generico: disco freno ant., filtro olio...'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
