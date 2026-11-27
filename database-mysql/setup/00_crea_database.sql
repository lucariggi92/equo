-- =====================================================================
-- EQUO — 00 · I due database
-- Da eseguire come root (MySQL 8.4), con crea_database_root.ps1, che subito dopo esegue
-- 01_utenti_separati.local.sql (i tre utenti e i loro permessi). Rieseguibile.
--
--   equo       il database vero, usato dal sito e dall'area riservata
--   equo_test  copia per i test automatici: si può svuotare e ricreare senza toccare i dati veri
--
-- Nessuna password in questo file. Le tabelle non si creano qui: le crea Laravel con le migration
-- (utente equo_schema). Fino al 27/11/2026 questo script creava anche l'utente equo_app, non più usato.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS equo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

CREATE DATABASE IF NOT EXISTS equo_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
