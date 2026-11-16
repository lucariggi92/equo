<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `intervention_components` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE intervention_components (
  variante_id    SMALLINT UNSIGNED NOT NULL,
  intervento_id  TINYINT UNSIGNED NOT NULL,
  componente_id  SMALLINT UNSIGNED NOT NULL,
  quantita       DECIMAL(6,2) NOT NULL COMMENT 'Nell''unità del componente (litri per l''olio)',
  PRIMARY KEY (variante_id, intervento_id, componente_id),
  KEY idx_intervention_components_componente (componente_id),
  KEY idx_intervention_components_intervento (intervento_id),
  CONSTRAINT fk_intervention_components_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_intervention_components_intervento FOREIGN KEY (intervento_id) REFERENCES interventions (id),
  CONSTRAINT fk_intervention_components_componente FOREIGN KEY (componente_id) REFERENCES components (id),
  CONSTRAINT ck_intervention_components_quantita CHECK (quantita > 0)
) ENGINE=InnoDB COMMENT='Per questa variante, in questo intervento, serve questo componente in questa quantità. Intervento e componente insieme: un intervento ha più pezzi e lo stesso pezzo sta in più interventi'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_components');
    }
};
