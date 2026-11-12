-- =====================================================================
-- EQUO — 01 · Schema del database (MySQL 8.4, InnoDB, utf8mb4)
--
-- Convenzioni
--   * Tutti gli importi sono NETTI di IVA. L'IVA si applica una sola volta,
--     alla fine del calcolo (aliquota in `settings`).
--   * Nomi delle tabelle in inglese, nomi delle colonne in italiano.
--   * Prezzi unitari DECIMAL(10,4); tariffe DECIMAL(10,2); quantità e ore DECIMAL(6,2).
--   * Storico: le righe con validità hanno valido_dal / valido_al.
--     La riga corrente ha valido_al = NULL ed è unica (colonna generata `corrente`).
--   * Nessun dato personale: le richieste si salvano anonime in `quote_requests`
--     (niente nome, email, telefono, IP).
--
-- Rieseguibile: elimina e ricrea tutte le tabelle (i dati vanno persi).
-- =====================================================================

USE equo;

SET FOREIGN_KEY_CHECKS = 0;
-- Nomi vecchi (italiano, fino al 2026-11-12)
DROP TABLE IF EXISTS tarature, ricarichi, tariffe_manodopera, ore_stimate,
  interventi_esclusi, prezzi_campioni, prezzi_ricambi, applicazioni, ricambi,
  distinta_base, componenti, interventi, varianti, modelli, marche, regioni,
  fonti, impostazioni;
-- Nomi attuali
DROP TABLE IF EXISTS quote_requests, calibrations, markups, labor_rates, labor_hours,
  price_samples, part_prices, part_qualities, spare_parts, intervention_components,
  components, interventions, variants, car_models, brands, regions, sources, settings;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Anagrafiche di base
-- ---------------------------------------------------------------------

CREATE TABLE settings (
  chiave       VARCHAR(50)  NOT NULL,
  valore       VARCHAR(100) NOT NULL,
  descrizione  VARCHAR(255) NULL,
  PRIMARY KEY (chiave)
) ENGINE=InnoDB COMMENT='Parametri globali: IVA, soglie del verdetto, minimi';

CREATE TABLE regions (
  id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(40) NOT NULL,
  area  ENUM('nord','centro','sud') NOT NULL COMMENT 'Fascia usata per le assunzioni iniziali sulle tariffe',
  PRIMARY KEY (id),
  UNIQUE KEY uq_regions_nome (nome)
) ENGINE=InnoDB COMMENT='Le 20 regioni italiane';

CREATE TABLE sources (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo              ENUM('listino_ufficiale','ecommerce','catalogo_produttore',
                         'fattura','officina','assunzione','altro') NOT NULL,
  riferimento       VARCHAR(200) NOT NULL COMMENT 'Nome del sito, dell''officina o del documento',
  affidabilita      ENUM('basso','medio','elevato') NOT NULL COMMENT 'Rating della fonte (elenco-fonti.xlsx). basso: mai usata come valore; medio: solo come mediana di 3 campioni; elevato: basta un valore',
  url               VARCHAR(500) NULL,
  file              VARCHAR(255) NULL COMMENT 'Screenshot/PDF archiviato in LISTINI PREZZI',
  data_rilevazione  DATE NOT NULL,
  note              VARCHAR(500) NULL,
  PRIMARY KEY (id),
  KEY idx_sources_tipo (tipo, data_rilevazione),
  KEY idx_sources_affidabilita (affidabilita)
) ENGINE=InnoDB COMMENT='Prova di ogni dato: nessun prezzo senza fonte, ogni fonte con il suo grado di affidabilità';

-- ---------------------------------------------------------------------
-- Veicoli
-- ---------------------------------------------------------------------

CREATE TABLE brands (
  id    SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome  VARCHAR(60) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_brands_nome (nome)
) ENGINE=InnoDB;

CREATE TABLE car_models (
  id        SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  marca_id  SMALLINT UNSIGNED NOT NULL,
  nome      VARCHAR(60) NOT NULL,
  segmento  VARCHAR(30) NULL,
  info_completed  BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'TRUE quando i dati di tutte le varianti sono completi (controlli M7): solo allora il modello compare nel form',
  PRIMARY KEY (id),
  UNIQUE KEY uq_car_models (marca_id, nome),
  CONSTRAINT fk_car_models_marca FOREIGN KEY (marca_id) REFERENCES brands (id)
) ENGINE=InnoDB;

CREATE TABLE variants (
  id                SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  modello_id        SMALLINT UNSIGNED NOT NULL,
  etichetta         VARCHAR(80) NOT NULL COMMENT 'Testo mostrato all''utente, es. "1.2 benzina 69 CV 2012-2018"',
  codice_motore     VARCHAR(30) NULL,
  cilindrata        SMALLINT UNSIGNED NULL COMMENT 'cm3',
  potenza_kw        SMALLINT UNSIGNED NULL,
  alimentazione     ENUM('benzina','diesel','gpl','metano','ibrida','elettrica') NOT NULL,
  distribuzione     ENUM('cinghia','catena') NULL,
  olio_specifica    VARCHAR(40) NULL COMMENT 'es. 5W-30 C3; i litri stanno in intervention_components',
  anno_produzione_inizio  SMALLINT UNSIGNED NOT NULL COMMENT 'Primo anno in cui questa motorizzazione è stata prodotta',
  anno_produzione_fine    SMALLINT UNSIGNED NULL COMMENT 'Ultimo anno di produzione; NULL = ancora in produzione',
  PRIMARY KEY (id),
  UNIQUE KEY uq_variants (modello_id, etichetta),
  CONSTRAINT fk_variants_modello FOREIGN KEY (modello_id) REFERENCES car_models (id),
  CONSTRAINT ck_variants_anni CHECK (anno_produzione_fine IS NULL OR anno_produzione_fine >= anno_produzione_inizio),
  CONSTRAINT ck_variants_anno_inizio CHECK (anno_produzione_inizio BETWEEN 1980 AND 2100)
) ENGINE=InnoDB COMMENT='La chiave reale per i ricambi: motore e generazione. Il form filtra le varianti con l''anno di immatricolazione dell''utente';

-- ---------------------------------------------------------------------
-- Interventi e loro composizione
-- ---------------------------------------------------------------------

CREATE TABLE interventions (
  id           TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome         VARCHAR(80) NOT NULL,
  descrizione  VARCHAR(255) NULL,
  tipo         ENUM('pacchetto','singolo') NOT NULL,
  visibile_nel_form  BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'FALSE = non compare nel menu del form (es. intervento sospeso)',
  PRIMARY KEY (id),
  UNIQUE KEY uq_interventions_nome (nome)
) ENGINE=InnoDB COMMENT='Elenco interventi del form: pacchetti (tagliando) e pezzi singoli, stesso calcolo';

CREATE TABLE components (
  id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  descrizione  VARCHAR(80) NOT NULL,
  categoria    ENUM('filtri','olio','accensione','freni','distribuzione','frizione','batteria') NOT NULL,
  unita        ENUM('pezzo','set','kit','litro') NOT NULL COMMENT 'Unità a cui si riferiscono quantità e prezzo',
  PRIMARY KEY (id),
  UNIQUE KEY uq_components_descrizione (descrizione)
) ENGINE=InnoDB COMMENT='Slot generico: disco freno ant., filtro olio...';

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
) ENGINE=InnoDB COMMENT='Per questa variante, in questo intervento, serve questo componente in questa quantità. Intervento e componente insieme: un intervento ha più pezzi e lo stesso pezzo sta in più interventi';


-- ---------------------------------------------------------------------
-- Ricambi e prezzi
-- ---------------------------------------------------------------------

CREATE TABLE spare_parts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codice       VARCHAR(40) NOT NULL,
  produttore   VARCHAR(60) NOT NULL,
  descrizione  VARCHAR(200) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_spare_parts (produttore, codice)
) ENGINE=InnoDB COMMENT='Anagrafica dei codici: un codice si prezza una volta e vale per tutte le auto compatibili';

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
) ENGINE=InnoDB COMMENT='Per ogni variante e componente: il codice del ricambio in ciascuna qualità';

CREATE TABLE part_prices (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ricambio_id       INT UNSIGNED NOT NULL,
  prezzo_netto      DECIMAL(10,4) NOT NULL COMMENT 'Valore usato nel calcolo: listino o mediana dei campioni, IVA esclusa',
  tipo_fonte        ENUM('ufficiale','media') NOT NULL COMMENT 'Decide quale ricarico si applica',
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
  CONSTRAINT ck_part_prices_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal)
) ENGINE=InnoDB COMMENT='Prezzo per codice, con storico; i singoli campioni stanno in price_samples';

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
) ENGINE=InnoDB COMMENT='Ogni rilevazione con la sua fonte: listino ufficiale (1) o campioni di mercato (3)';

-- ---------------------------------------------------------------------
-- Manodopera e ricarico
-- ---------------------------------------------------------------------

CREATE TABLE labor_hours (
  variante_id    SMALLINT UNSIGNED NOT NULL,
  intervento_id  TINYINT UNSIGNED NOT NULL,
  ore            DECIMAL(6,2) NOT NULL,
  origine        ENUM('assunzione','reale') NOT NULL,
  fonte_id       INT UNSIGNED NULL,
  note           VARCHAR(255) NULL,
  PRIMARY KEY (variante_id, intervento_id),
  KEY idx_labor_hours_intervento (intervento_id),
  CONSTRAINT fk_labor_hours_variante   FOREIGN KEY (variante_id)   REFERENCES variants (id),
  CONSTRAINT fk_labor_hours_intervento FOREIGN KEY (intervento_id) REFERENCES interventions (id),
  CONSTRAINT fk_labor_hours_fonte      FOREIGN KEY (fonte_id)      REFERENCES sources (id),
  CONSTRAINT ck_labor_hours CHECK (ore > 0)
) ENGINE=InnoDB COMMENT='Tempario: ore per intervento (anche pezzi singoli); il minimo è in settings';

CREATE TABLE labor_rates (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  regione_id            TINYINT UNSIGNED NOT NULL,
  tipo_officina         ENUM('concessionaria','generica','catena') NOT NULL,
  tariffa_oraria_netta  DECIMAL(10,2) NOT NULL,
  origine               ENUM('assunzione','reale') NOT NULL,
  n_campioni            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  data_rilevazione      DATE NOT NULL,
  valido_dal            DATE NOT NULL,
  valido_al             DATE NULL,
  corrente              TINYINT AS (IF(valido_al IS NULL, 1, NULL)) STORED,
  fonte_id              INT UNSIGNED NULL,
  note                  VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_labor_rates_corrente (regione_id, tipo_officina, corrente),
  KEY idx_labor_rates (regione_id, tipo_officina, valido_al),
  CONSTRAINT fk_labor_rates_regione FOREIGN KEY (regione_id) REFERENCES regions (id),
  CONSTRAINT fk_labor_rates_fonte   FOREIGN KEY (fonte_id)   REFERENCES sources (id),
  CONSTRAINT ck_labor_rates_valore CHECK (tariffa_oraria_netta > 0),
  CONSTRAINT ck_labor_rates_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal)
) ENGINE=InnoDB COMMENT='Tariffa oraria netta per regione e tipo officina, con storico';

CREATE TABLE markups (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_officina  ENUM('concessionaria','generica','catena') NOT NULL,
  tipo_fonte     ENUM('ufficiale','media') NOT NULL COMMENT 'Tipo di prezzo a cui si applica',
  fattore        DECIMAL(6,4) NOT NULL COMMENT 'Additivo: 0.18 = +18%; può essere 0 o negativo',
  origine        ENUM('assunzione','taratura') NOT NULL,
  n_campioni     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  valido_dal     DATE NOT NULL,
  valido_al      DATE NULL,
  corrente       TINYINT AS (IF(valido_al IS NULL, 1, NULL)) STORED,
  note           VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_markups_corrente (tipo_officina, tipo_fonte, corrente),
  KEY idx_markups (tipo_officina, tipo_fonte, valido_al),
  CONSTRAINT ck_markups_fattore CHECK (fattore BETWEEN -0.5000 AND 2.0000),
  CONSTRAINT ck_markups_validita CHECK (valido_al IS NULL OR valido_al >= valido_dal)
) ENGINE=InnoDB COMMENT='Ricarico sui ricambi per tipo officina e tipo di prezzo (niente regione)';

CREATE TABLE calibrations (
  id                        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fonte_id                  INT UNSIGNED NOT NULL COMMENT 'La fattura o il preventivo reale',
  ricambio_id               INT UNSIGNED NOT NULL,
  tipo_officina             ENUM('concessionaria','generica','catena') NOT NULL,
  regione_id                TINYINT UNSIGNED NOT NULL,
  prezzo_fattura_netto      DECIMAL(10,4) NOT NULL,
  prezzo_riferimento_netto  DECIMAL(10,4) NOT NULL COMMENT 'Prezzo corrente dello stesso codice in part_prices',
  tipo_fonte_riferimento    ENUM('ufficiale','media') NOT NULL,
  rapporto                  DECIMAL(10,4) AS (prezzo_fattura_netto / prezzo_riferimento_netto) STORED,
  data_fattura              DATE NOT NULL,
  PRIMARY KEY (id),
  KEY idx_calibrations (tipo_officina, tipo_fonte_riferimento),
  CONSTRAINT fk_calibrations_fonte    FOREIGN KEY (fonte_id)    REFERENCES sources (id),
  CONSTRAINT fk_calibrations_ricambio FOREIGN KEY (ricambio_id) REFERENCES spare_parts (id),
  CONSTRAINT fk_calibrations_regione  FOREIGN KEY (regione_id)  REFERENCES regions (id),
  CONSTRAINT ck_calibrations_prezzi CHECK (prezzo_fattura_netto > 0 AND prezzo_riferimento_netto > 0)
) ENGINE=InnoDB COMMENT='Pezzi letti su fatture reali: la mediana di rapporto - 1 dà il fattore in markups';

-- ---------------------------------------------------------------------
-- Richieste degli utenti (anonime)
-- ---------------------------------------------------------------------

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
) ENGINE=InnoDB COMMENT='Ogni verifica fatta dagli utenti, senza dati personali: statistiche e interventi da aggiungere';
