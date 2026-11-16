-- =====================================================================
-- EQUO — M8 · Taratura senza fatture
--
-- Crea la procedura taratura. Va caricata dopo calcolo_stima.sql (usa prepara_stima):
--
--   source queries/calcolo_stima.sql;
--   source queries/taratura.sql;
--   CALL taratura();
--
-- Le fatture reali non ci sono, quindi la taratura usa valori pubblicati (decisione PM del 2026-11-16):
--   1. ore: tempi per versione e motore da un tempario pubblicato (labor_hours.origine = 'tempario');
--   2. tariffe orarie: indagini e riviste nella tabella market_benchmarks (tipo_dato = 'tariffa_oraria');
--   3. totali per intervento: prezzi al cliente pubblicati (tipo_dato = 'prezzo_intervento'), solo come controllo.
--
-- Regola per le tariffe, con i parametri in settings:
--   * ogni fonte vale una volta (le sue righe per area si mediano tra loro), solo se riferita agli ultimi
--     taratura_anni_max anni e se dice il tipo di officina;
--   * meno di taratura_min_fonti fonti               -> "fonti insufficienti": resta il valore EQUO;
--   * fonti distanti tra loro oltre taratura_dispersione -> "fonti discordanti": resta il valore EQUO;
--   * mediana entro ±taratura_scarto dal valore EQUO -> "confermata";
--   * altrimenti                                     -> "da correggere", con il fattore da applicare.
-- La procedura non cambia nessun valore: lo dice. La correzione, quando servirà, si applica dal foglio Tariffe.
--
-- Restituisce cinque tabelle:
--   1. ore per variante: quante vengono dal tempario e quante sono ancora assunzioni;
--   2. ore dal tempario, riga per riga, con il valore D6 che hanno sostituito (nella nota);
--   3. tariffe: ogni fonte confrontata con il valore EQUO della stessa area;
--   4. tariffe: esito per tipo di officina;
--   5. totali: prezzo pubblicato contro stima EQUO (qualità equivalente, media delle regioni dell'area).
-- =====================================================================

USE equo;

DROP PROCEDURE IF EXISTS taratura;

DELIMITER $$
CREATE PROCEDURE taratura()
BEGIN
  DECLARE v_iva, v_min_fonti, v_scarto, v_disp, v_anni DECIMAL(10,4);
  DECLARE v_fine INT DEFAULT 0;
  DECLARE b_id INT UNSIGNED;
  DECLARE b_var SMALLINT UNSIGNED;
  DECLARE b_int TINYINT UNSIGNED;
  DECLARE b_off VARCHAR(20);
  DECLARE b_area VARCHAR(12);
  DECLARE v_reg TINYINT UNSIGNED;
  DECLARE cur_b CURSOR FOR
    SELECT id, variante_id, intervento_id, COALESCE(tipo_officina, 'generica'), area
    FROM market_benchmarks WHERE tipo_dato = 'prezzo_intervento';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_fine = 1;

  SELECT CAST(valore AS DECIMAL(10,4)) / 100 INTO v_iva      FROM settings WHERE chiave = 'iva_pct';
  SELECT CAST(valore AS DECIMAL(10,4))       INTO v_min_fonti FROM settings WHERE chiave = 'taratura_min_fonti';
  SELECT CAST(valore AS DECIMAL(10,4))       INTO v_scarto    FROM settings WHERE chiave = 'taratura_scarto';
  SELECT CAST(valore AS DECIMAL(10,4))       INTO v_disp      FROM settings WHERE chiave = 'taratura_dispersione';
  SELECT CAST(valore AS DECIMAL(10,4))       INTO v_anni      FROM settings WHERE chiave = 'taratura_anni_max';

  -- 1. ore per variante
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante,
         SUM(lh.origine = 'tempario')   AS ore_da_tempario,
         SUM(lh.origine = 'assunzione') AS ore_ancora_assunte,
         SUM(lh.origine = 'reale')      AS ore_reali
  FROM variants v
  JOIN car_models m ON m.id = v.modello_id
  LEFT JOIN labor_hours lh ON lh.variante_id = v.id
  GROUP BY v.id
  ORDER BY v.id;

  -- 2. ore dal tempario
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante, i.nome AS intervento, lh.ore, s.riferimento AS fonte, lh.note
  FROM labor_hours lh
  JOIN variants v      ON v.id = lh.variante_id
  JOIN car_models m    ON m.id = v.modello_id
  JOIN interventions i ON i.id = lh.intervento_id
  LEFT JOIN sources s  ON s.id = lh.fonte_id
  WHERE lh.origine = 'tempario'
  ORDER BY v.id, i.id;

  -- valore EQUO corrente per tipo di officina e area (media delle regioni)
  DROP TEMPORARY TABLE IF EXISTS tmp_equo;
  CREATE TEMPORARY TABLE tmp_equo (tipo_officina VARCHAR(20), area VARCHAR(12), valore DECIMAL(10,4));
  INSERT INTO tmp_equo
  SELECT lr.tipo_officina, g.area, AVG(lr.tariffa_oraria_netta)
  FROM labor_rates lr JOIN regions g ON g.id = lr.regione_id
  WHERE lr.valido_al IS NULL GROUP BY lr.tipo_officina, g.area;
  INSERT INTO tmp_equo
  SELECT tipo_officina, 'nazionale', AVG(tariffa_oraria_netta)
  FROM labor_rates WHERE valido_al IS NULL GROUP BY tipo_officina;

  -- ogni riga di tariffa pubblicata, al netto di IVA, contro il valore EQUO della stessa area
  DROP TEMPORARY TABLE IF EXISTS tmp_bench;
  CREATE TEMPORARY TABLE tmp_bench (
    fonte VARCHAR(200), tipo_officina VARCHAR(20), area VARCHAR(12), anno SMALLINT UNSIGNED,
    pubblicato_netto DECIMAL(10,2), valore_equo DECIMAL(10,2), scarto DECIMAL(10,4), conta TINYINT, motivo VARCHAR(80)
  );
  INSERT INTO tmp_bench
  SELECT s.riferimento, b.tipo_officina, b.area, b.anno_riferimento,
         (b.valore_min + b.valore_max) / 2 / IF(b.iva_inclusa, 1 + v_iva, 1),
         e.valore,
         (b.valore_min + b.valore_max) / 2 / IF(b.iva_inclusa, 1 + v_iva, 1) / e.valore - 1,
         (b.tipo_officina IS NOT NULL AND b.anno_riferimento >= YEAR(CURDATE()) - v_anni),
         CASE WHEN b.tipo_officina IS NULL THEN 'non conta: tipo di officina non indicato'
              WHEN b.anno_riferimento < YEAR(CURDATE()) - v_anni THEN 'non conta: dato troppo vecchio'
              ELSE 'conta' END
  FROM market_benchmarks b
  JOIN sources s ON s.id = b.fonte_id
  LEFT JOIN tmp_equo e ON e.tipo_officina = b.tipo_officina AND e.area = b.area
  WHERE b.tipo_dato = 'tariffa_oraria';

  -- 3. dettaglio delle fonti
  SELECT fonte, tipo_officina, area, anno, pubblicato_netto, valore_equo, ROUND(scarto * 100, 1) AS scarto_pct, motivo
  FROM tmp_bench ORDER BY tipo_officina, fonte, area;

  -- una riga per fonte (le sue aree mediate tra loro)
  DROP TEMPORARY TABLE IF EXISTS tmp_fonte;
  CREATE TEMPORARY TABLE tmp_fonte (tipo_officina VARCHAR(20), fonte VARCHAR(200), scarto DECIMAL(10,4));
  INSERT INTO tmp_fonte SELECT tipo_officina, fonte, AVG(scarto) FROM tmp_bench WHERE conta = 1 GROUP BY tipo_officina, fonte;

  DROP TEMPORARY TABLE IF EXISTS tmp_med;
  CREATE TEMPORARY TABLE tmp_med (tipo_officina VARCHAR(20), mediana DECIMAL(10,4));
  INSERT INTO tmp_med
  SELECT tipo_officina, AVG(scarto)
  FROM (SELECT tipo_officina, scarto,
               ROW_NUMBER() OVER (PARTITION BY tipo_officina ORDER BY scarto) AS rn,
               COUNT(*) OVER (PARTITION BY tipo_officina) AS n
        FROM tmp_fonte) x
  WHERE rn IN (FLOOR((n + 1) / 2), CEIL((n + 1) / 2))
  GROUP BY tipo_officina;

  -- 4. esito per tipo di officina
  SELECT t.tipo AS tipo_officina,
         ROUND(e.valore, 2) AS valore_equo_nazionale,
         COALESCE(f.n, 0) AS fonti_valide,
         ROUND(e.valore * (1 + md.mediana), 2) AS mediana_fonti,
         ROUND(md.mediana * 100, 1) AS scarto_pct,
         ROUND(f.dispersione * 100, 1) AS dispersione_pct,
         CASE WHEN COALESCE(f.n, 0) < v_min_fonti THEN 'fonti insufficienti: resta il valore EQUO'
              WHEN f.dispersione > v_disp THEN 'fonti discordanti: resta il valore EQUO'
              WHEN ABS(md.mediana) <= v_scarto THEN 'confermata'
              ELSE CONCAT('da correggere: moltiplicare le tariffe per ', ROUND(1 + md.mediana, 3)) END AS esito
  FROM (SELECT 'concessionaria' AS tipo UNION ALL SELECT 'generica' UNION ALL SELECT 'catena') t
  LEFT JOIN tmp_equo e ON e.tipo_officina = t.tipo AND e.area = 'nazionale'
  LEFT JOIN (SELECT tipo_officina, COUNT(*) AS n, MAX(1 + scarto) / MIN(1 + scarto) - 1 AS dispersione
             FROM tmp_fonte GROUP BY tipo_officina) f ON f.tipo_officina = t.tipo
  LEFT JOIN tmp_med md ON md.tipo_officina = t.tipo
  ORDER BY FIELD(t.tipo, 'concessionaria', 'generica', 'catena');

  -- totali: stima EQUO per ogni prezzo pubblicato, in ogni regione dell'area
  DROP TEMPORARY TABLE IF EXISTS tmp_tot;
  CREATE TEMPORARY TABLE tmp_tot (bench_id INT UNSIGNED, regione_id TINYINT UNSIGNED, stima DECIMAL(10,2), completo TINYINT);
  OPEN cur_b;
  leggi: LOOP
    FETCH cur_b INTO b_id, b_var, b_int, b_off, b_area;
    IF v_fine = 1 THEN LEAVE leggi; END IF;
    SET v_reg = (SELECT MIN(id) FROM regions WHERE b_area = 'nazionale' OR area = b_area);
    WHILE v_reg IS NOT NULL DO
      CALL prepara_stima(b_var, b_int, 'equivalente', v_reg, b_off);
      INSERT INTO tmp_tot SELECT b_id, v_reg, stima_iva_inclusa, completo FROM tmp_riepilogo;
      SET v_reg = (SELECT MIN(id) FROM regions WHERE id > v_reg AND (b_area = 'nazionale' OR area = b_area));
    END WHILE;
  END LOOP;
  CLOSE cur_b;

  -- 5. totali pubblicati contro stima EQUO
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante, i.nome AS intervento,
         COALESCE(b.tipo_officina, 'generica (non indicata)') AS officina,
         b.valore_min AS pubblicato_da, b.valore_max AS pubblicato_a,
         IF(b.iva_inclusa, 'sì', 'no') AS pubblicato_iva_inclusa,
         ROUND(AVG(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva), 2) AS stima_equo,
         ROUND(MIN(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva), 2) AS stima_regione_min,
         ROUND(MAX(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva), 2) AS stima_regione_max,
         ROUND((AVG(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva) / ((b.valore_min + b.valore_max) / 2) - 1) * 100, 1) AS scarto_pct,
         CASE WHEN AVG(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva) > b.valore_max THEN 'stima sopra il pubblicato'
              WHEN AVG(t.stima) / IF(b.iva_inclusa, 1, 1 + v_iva) < b.valore_min THEN 'stima sotto il pubblicato'
              ELSE 'dentro' END AS posizione,
         MIN(t.completo) AS completo,
         s.riferimento AS fonte
  FROM market_benchmarks b
  JOIN tmp_tot t       ON t.bench_id = b.id
  JOIN variants v      ON v.id = b.variante_id
  JOIN car_models m    ON m.id = v.modello_id
  JOIN interventions i ON i.id = b.intervento_id
  JOIN sources s       ON s.id = b.fonte_id
  GROUP BY b.id
  ORDER BY v.id, i.id, b.tipo_officina;

  DROP TEMPORARY TABLE IF EXISTS tmp_equo;
  DROP TEMPORARY TABLE IF EXISTS tmp_bench;
  DROP TEMPORARY TABLE IF EXISTS tmp_fonte;
  DROP TEMPORARY TABLE IF EXISTS tmp_med;
  DROP TEMPORARY TABLE IF EXISTS tmp_tot;
  DROP TEMPORARY TABLE IF EXISTS tmp_righe;
  DROP TEMPORARY TABLE IF EXISTS tmp_riepilogo;
END$$
DELIMITER ;
