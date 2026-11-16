-- =====================================================================
-- EQUO — 02 · Database di prova per Laravel (task L2)
-- Da eseguire come root, dopo 01_utenti_separati.sql. Rieseguibile.
--
-- equo_test è una copia vuota della struttura, costruita dalle migration di Laravel:
--   * serve a verificare che le migration producano tabelle identiche a quelle di `equo`;
--   * serve ai test automatici, che possono svuotarla e ricrearla senza toccare i dati veri.
-- Nessuna password in questo file: usa gli utenti già creati da 01_utenti_separati.sql.
-- I permessi di equo_api e equo_import su equo_test si danno tabella per tabella con 01_utenti_separati.sql,
-- dopo che le migration hanno creato le tabelle.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS equo_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

GRANT ALL PRIVILEGES ON equo_test.* TO 'equo_schema'@'localhost';

SHOW GRANTS FOR 'equo_schema'@'localhost';
