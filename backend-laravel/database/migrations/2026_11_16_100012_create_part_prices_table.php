<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Tabella `part_prices` di EQUO.
 * Il CREATE TABLE è lo stesso di database-mysql/schema/01_schema.sql (provato in MySQL nella fase 1):
 * vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi restano identici.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE part_prices (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ricambio_id       INT UNSIGNED NOT NULL,
  prezzo_netto      DECIMAL(10,4) NOT NULL COMMENT 'Valore usato nel calcolo: listino o mediana dei campioni, IVA esclusa',
  tipo_fonte        ENUM('ufficiale','media') NOT NULL COMMENT 'Decide quale ricarico si applica',
  metodo            ENUM('rilevato','indicativo','rapporto_qualita','mediana_marca','mediana_categoria') NOT NULL DEFAULT 'rilevato'
                    COMMENT 'Scala D5: rilevato = 2+ negozi indipendenti; indicativo = 1 negozio; gli altri sono stime statistiche',
  da_avvalorare     BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'TRUE per tutto ciò che non è rilevato: va confermato con un prezzo reale',
  nota_stima        VARCHAR(255) NULL COMMENT 'Come è stata calcolata la stima (base, rapporto, campioni)',
  n_campioni        TINYINT UNSIGNED NOT NULL,
  prezzo_min        DECIMAL(10,4) NULL,
  prezzo_max        DECIMAL(10,4) NULL,
  data_rilevazione  DATE NOT NULL,
  valido_dal        DATE NOT NULL,
  valido_al         DATE NULL,
  corrente          TINYINT AS (IF(valido_al IS NULL, 1, NULL)) STORED,
  PRIMARY KEY (id),
  UNIQUE KEY uq_part_prices_corrente (ricambio_id, corrente),
  KEY idx_part_prices_ricambio (ricambio_id, valido_al),
  CONSTRAINT fk_part_prices_ricambio FOREIGN KEY (ricambio_id) REFERENCES spare_parts (id),
  CONSTRAINT ck_part_prices_netto CHECK (prezzo_netto > 0),
  CONSTRAINT ck_part_prices_campioni CHECK (n_campioni >= 1),
  CONSTRAINT ck_part_prices_range CHECK (prezzo_min IS NULL OR prezzo_max IS NULL
                                    OR (prezzo_min <= prezzo_netto AND prezzo_netto <= prezzo_max)),
  CONSTRAINT ck_part_prices_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal),
  CONSTRAINT ck_part_prices_metodo CHECK ((metodo = 'rilevato') = (da_avvalorare = FALSE))
) ENGINE=InnoDB COMMENT='Prezzo per codice, con storico; i singoli campioni stanno in price_samples; le stime D5 sono marcate da_avvalorare'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('part_prices');
    }
};
