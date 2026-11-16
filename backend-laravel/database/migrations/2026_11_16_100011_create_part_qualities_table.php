<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `part_qualities` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE part_qualities (
  variante_id    SMALLINT UNSIGNED NOT NULL,
  componente_id  SMALLINT UNSIGNED NOT NULL,
  qualita        ENUM('originale','equivalente','economico') NOT NULL,
  ricambio_id    INT UNSIGNED NOT NULL,
  PRIMARY KEY (variante_id, componente_id, qualita),
  KEY idx_part_qualities_ricambio (ricambio_id),
  CONSTRAINT fk_part_qualities_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_part_qualities_componente FOREIGN KEY (componente_id) REFERENCES components (id),
  CONSTRAINT fk_part_qualities_ricambio   FOREIGN KEY (ricambio_id)   REFERENCES spare_parts (id)
) ENGINE=InnoDB COMMENT='Per ogni variante e componente: il codice del ricambio in ciascuna qualità'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('part_qualities');
    }
};
