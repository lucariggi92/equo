-- =====================================================================
-- EQUO — 00 · Database e utente applicativo
-- Da eseguire UNA volta, come root (MySQL 8.4).
--
-- Questo file è il modello versionato: la password è un segnaposto.
-- La copia con la password vera è 00_crea_database.local.sql (non va su git).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS equo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE USER IF NOT EXISTS 'equo_app'@'localhost'
  IDENTIFIED BY 'CAMBIA_QUESTA_PASSWORD';

-- Pieni diritti solo sul database equo (serve anche per creare le tabelle
-- e, più avanti, per le migration Laravel). Nessun diritto sugli altri database.
GRANT ALL PRIVILEGES ON equo.* TO 'equo_app'@'localhost';

FLUSH PRIVILEGES;
