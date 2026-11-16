<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `sources` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE sources (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo              ENUM('listino_ufficiale','ecommerce','catalogo_produttore',
                         'fattura','officina','tempario','assunzione','altro') NOT NULL COMMENT 'tempario: tempi di lavoro pubblicati per modello e motore',
  riferimento       VARCHAR(200) NOT NULL COMMENT 'Nome del sito, dell''officina o del documento',
  affidabilita      ENUM('basso','medio','elevato') NOT NULL COMMENT 'Rating della fonte (elenco-fonti.xlsx). basso: mai usata come valore; medio: solo come mediana di almeno 2 negozi indipendenti; elevato: basta un valore',
  url               VARCHAR(500) NULL,
  file              VARCHAR(255) NULL COMMENT 'Screenshot/PDF archiviato in LISTINI PREZZI',
  data_rilevazione  DATE NOT NULL,
  note              VARCHAR(500) NULL,
  PRIMARY KEY (id),
  KEY idx_sources_tipo (tipo, data_rilevazione),
  KEY idx_sources_affidabilita (affidabilita)
) ENGINE=InnoDB COMMENT='Prova di ogni dato: nessun prezzo senza fonte, ogni fonte con il suo grado di affidabilità'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
