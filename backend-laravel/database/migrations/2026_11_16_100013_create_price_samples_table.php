<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `price_samples` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE price_samples (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  prezzo_id          INT UNSIGNED NOT NULL,
  fonte_id           INT UNSIGNED NOT NULL,
  prezzo_fonte       DECIMAL(10,4) NOT NULL COMMENT 'Come esposto dalla fonte',
  iva_inclusa        BOOLEAN NOT NULL,
  iva_fonte_pct      DECIMAL(5,2) NOT NULL DEFAULT 22.00,
  prezzo_netto       DECIMAL(10,4) NOT NULL COMMENT 'prezzo_fonte senza IVA',
  scartato           BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Fuori soglia rispetto alla mediana: annotato, non usato',
  note               VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_price_samples_prezzo (prezzo_id),
  KEY idx_price_samples_fonte (fonte_id),
  CONSTRAINT fk_price_samples_prezzo FOREIGN KEY (prezzo_id) REFERENCES part_prices (id) ON DELETE CASCADE,
  CONSTRAINT fk_price_samples_fonte  FOREIGN KEY (fonte_id)  REFERENCES sources (id),
  CONSTRAINT ck_price_samples_prezzo CHECK (prezzo_fonte > 0 AND prezzo_netto > 0)
) ENGINE=InnoDB COMMENT='Ogni rilevazione con la sua fonte: listino ufficiale (1) o campioni di mercato (3)'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('price_samples');
    }
};
