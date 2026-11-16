<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `reference_prices` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE reference_prices (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  componente_id     SMALLINT UNSIGNED NOT NULL,
  qualita           ENUM('originale','equivalente','economico') NOT NULL,
  produttore        VARCHAR(60) NOT NULL,
  codice            VARCHAR(40) NOT NULL COMMENT 'Anche codici di altre auto: servono a costruire la statistica',
  sottotipo         VARCHAR(40) NULL COMMENT 'Stessa classificazione di spare_parts.sottotipo',
  prezzo_netto      DECIMAL(10,4) NOT NULL,
  fonte_id          INT UNSIGNED NOT NULL,
  data_rilevazione  DATE NOT NULL,
  note              VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reference_prices (produttore, codice, fonte_id),
  KEY idx_reference_categoria (componente_id, qualita),
  KEY idx_reference_marca (produttore, componente_id),
  CONSTRAINT fk_reference_prices_componente FOREIGN KEY (componente_id) REFERENCES components (id),
  CONSTRAINT fk_reference_prices_fonte      FOREIGN KEY (fonte_id)      REFERENCES sources (id),
  CONSTRAINT ck_reference_prices_netto CHECK (prezzo_netto > 0)
) ENGINE=InnoDB COMMENT='Prezzi di mercato di riferimento per categoria e marca: base statistica delle stime D5'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_prices');
    }
};
