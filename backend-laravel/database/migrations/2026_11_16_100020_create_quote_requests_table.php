<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `quote_requests` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE quote_requests (
  id                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  creata_il              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  variante_id            SMALLINT UNSIGNED NULL,
  anno_immatricolazione  SMALLINT UNSIGNED NULL,
  intervento_id          TINYINT UNSIGNED NULL COMMENT 'Intervento scelto dal menu',
  altro_intervento       VARCHAR(255) NULL COMMENT 'Testo libero se l''intervento non è in elenco: indica cosa aggiungere in futuro',
  qualita                ENUM('originale','equivalente','economico') NULL,
  regione_id             TINYINT UNSIGNED NOT NULL,
  tipo_officina          ENUM('concessionaria','generica','catena') NOT NULL,
  importo_preventivo     DECIMAL(10,2) NOT NULL COMMENT 'Come scritto sul preventivo del meccanico',
  iva_inclusa            BOOLEAN NOT NULL DEFAULT TRUE,
  stima_calcolata        DECIMAL(10,2) NULL COMMENT 'Stima EQUO IVA inclusa; NULL se non calcolabile',
  scostamento_pct        DECIMAL(7,2) NULL,
  esito                  ENUM('equo','sopra_media','anomalo','dati_insufficienti','non_calcolabile') NULL,
  PRIMARY KEY (id),
  KEY idx_quote_requests_data (creata_il),
  KEY idx_quote_requests_intervento (intervento_id),
  CONSTRAINT fk_quote_requests_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_quote_requests_intervento FOREIGN KEY (intervento_id) REFERENCES interventions (id),
  CONSTRAINT fk_quote_requests_regione    FOREIGN KEY (regione_id)    REFERENCES regions (id),
  CONSTRAINT ck_quote_requests_intervento CHECK (intervento_id IS NOT NULL OR altro_intervento IS NOT NULL),
  CONSTRAINT ck_quote_requests_importo CHECK (importo_preventivo > 0),
  CONSTRAINT ck_quote_requests_anno CHECK (anno_immatricolazione IS NULL OR anno_immatricolazione BETWEEN 1980 AND 2100)
) ENGINE=InnoDB COMMENT='Ogni verifica fatta dagli utenti, senza dati personali: statistiche e interventi da aggiungere'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
