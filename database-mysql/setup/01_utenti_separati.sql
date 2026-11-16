-- =====================================================================
-- EQUO — 01 · Tre utenti MySQL separati (decisione D8, task L1; tabelle Laravel e equo_test dal task L2)
-- Da eseguire come root, dopo 00_crea_database.sql e 02_database_test.sql. Rieseguibile: riallinea password e permessi.
-- Generato da genera_utenti.py: modificare quello, non questo file.
--
-- Questo file è il modello versionato: le password sono segnaposto.
-- La copia con le password vere è 01_utenti_separati.local.sql (non va su git).
--
--   equo_api     usato dall'API per il sito React: legge il catalogo, aggiunge richieste anonime.
--                Non può modificare né cancellare niente: anche con il codice compromesso, MySQL rifiuta.
--   equo_import  usato dall'import e dall'area riservata: legge e scrive le tabelle dei dati.
--   equo_schema  solo per creare e modificare le tabelle (migration), lanciato a mano.
--
-- I permessi sono dati tabella per tabella: una tabella nuova non è accessibile finché non la si aggiunge qui.
-- L'utente equo_app di 00_crea_database.sql resta per gli script attuali (import Python, backup) fino al task L8.
-- Gli stessi permessi valgono per equo (dati veri) e equo_test (database di prova dei test automatici);
-- le tabelle devono esistere: equo_test si costruisce prima con le migration.
-- =====================================================================

-- equo_api: sola lettura del catalogo + inserimento in quote_requests; nessun accesso alle tabelle dell'area riservata
CREATE USER IF NOT EXISTS 'equo_api'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_API';
ALTER USER 'equo_api'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_API';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'equo_api'@'localhost';
GRANT SELECT ON equo.brands TO 'equo_api'@'localhost';
GRANT SELECT ON equo.car_models TO 'equo_api'@'localhost';
GRANT SELECT ON equo.components TO 'equo_api'@'localhost';
GRANT SELECT ON equo.intervention_components TO 'equo_api'@'localhost';
GRANT SELECT ON equo.interventions TO 'equo_api'@'localhost';
GRANT SELECT ON equo.labor_hours TO 'equo_api'@'localhost';
GRANT SELECT ON equo.labor_rates TO 'equo_api'@'localhost';
GRANT SELECT ON equo.market_benchmarks TO 'equo_api'@'localhost';
GRANT SELECT ON equo.markups TO 'equo_api'@'localhost';
GRANT SELECT ON equo.part_prices TO 'equo_api'@'localhost';
GRANT SELECT ON equo.part_qualities TO 'equo_api'@'localhost';
GRANT SELECT ON equo.price_samples TO 'equo_api'@'localhost';
GRANT SELECT ON equo.reference_prices TO 'equo_api'@'localhost';
GRANT SELECT ON equo.regions TO 'equo_api'@'localhost';
GRANT SELECT ON equo.settings TO 'equo_api'@'localhost';
GRANT SELECT ON equo.sources TO 'equo_api'@'localhost';
GRANT SELECT ON equo.spare_parts TO 'equo_api'@'localhost';
GRANT SELECT ON equo.variants TO 'equo_api'@'localhost';
GRANT INSERT ON equo.quote_requests TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.brands TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.car_models TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.components TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.intervention_components TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.interventions TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.labor_hours TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.labor_rates TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.market_benchmarks TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.markups TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.part_prices TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.part_qualities TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.price_samples TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.reference_prices TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.regions TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.settings TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.sources TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.spare_parts TO 'equo_api'@'localhost';
GRANT SELECT ON equo_test.variants TO 'equo_api'@'localhost';
GRANT INSERT ON equo_test.quote_requests TO 'equo_api'@'localhost';

-- equo_import: dati (lettura e scrittura), procedure, tabelle di Laravel per l'area riservata;
-- il registro delle azioni si può solo leggere e aggiungere, mai modificare o cancellare
CREATE USER IF NOT EXISTS 'equo_import'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_IMPORT';
ALTER USER 'equo_import'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_IMPORT';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.brands TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.car_models TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.components TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.intervention_components TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.interventions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.labor_hours TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.labor_rates TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.market_benchmarks TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.markups TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.part_prices TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.part_qualities TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.price_samples TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.quote_requests TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.reference_prices TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.regions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.settings TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.sources TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.spare_parts TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.variants TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.users TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.sessions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.cache TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo.cache_locks TO 'equo_import'@'localhost';
GRANT SELECT, INSERT ON equo.admin_audit_log TO 'equo_import'@'localhost';
GRANT EXECUTE, CREATE TEMPORARY TABLES ON equo.* TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.brands TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.car_models TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.components TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.intervention_components TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.interventions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.labor_hours TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.labor_rates TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.market_benchmarks TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.markups TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.part_prices TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.part_qualities TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.price_samples TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.quote_requests TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.reference_prices TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.regions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.settings TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.sources TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.spare_parts TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.variants TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.users TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.sessions TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.cache TO 'equo_import'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON equo_test.cache_locks TO 'equo_import'@'localhost';
GRANT SELECT, INSERT ON equo_test.admin_audit_log TO 'equo_import'@'localhost';
GRANT EXECUTE, CREATE TEMPORARY TABLES ON equo_test.* TO 'equo_import'@'localhost';

-- equo_schema: struttura del database (migration); mai usato dall'app in esercizio
CREATE USER IF NOT EXISTS 'equo_schema'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_SCHEMA';
ALTER USER 'equo_schema'@'localhost' IDENTIFIED BY 'CAMBIA_PASSWORD_SCHEMA';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'equo_schema'@'localhost';
GRANT ALL PRIVILEGES ON equo.* TO 'equo_schema'@'localhost';
GRANT ALL PRIVILEGES ON equo_test.* TO 'equo_schema'@'localhost';

-- controllo finale
SHOW GRANTS FOR 'equo_api'@'localhost';
SHOW GRANTS FOR 'equo_import'@'localhost';
SHOW GRANTS FOR 'equo_schema'@'localhost';
