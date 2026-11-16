<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `car_models` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE car_models (
  id        SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  marca_id  SMALLINT UNSIGNED NOT NULL,
  nome      VARCHAR(60) NOT NULL,
  segmento  VARCHAR(30) NULL,
  info_completed  BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'TRUE quando i dati di tutte le varianti sono completi (controlli M7): solo allora il modello compare nel form',
  PRIMARY KEY (id),
  UNIQUE KEY uq_car_models (marca_id, nome),
  CONSTRAINT fk_car_models_marca FOREIGN KEY (marca_id) REFERENCES brands (id)
) ENGINE=InnoDB
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
