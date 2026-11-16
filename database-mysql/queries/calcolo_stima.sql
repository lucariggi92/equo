-- =====================================================================
-- EQUO — M6 · Calcolo della stima e del verdetto (design §9)
--
-- Crea due procedure:
--   prepara_stima  fa il calcolo e lascia il risultato nelle tabelle temporanee tmp_righe e tmp_riepilogo
--                  (la usano calcola_stima e la taratura M8, così il calcolo esiste in un solo posto);
--   calcola_stima  la chiama e restituisce dettaglio, riepilogo e verdetto.
-- Uso, dopo `source queries/calcolo_stima.sql`:
--
--   CALL calcola_stima(variante_id, intervento_id, 'equivalente', regione_id, 'generica', 250.00);
--
-- Parametri:
--   p_variante, p_intervento   id da variants e interventions
--   p_qualita                  'originale' | 'equivalente' | 'economico'
--   p_regione                  id da regions
--   p_officina                 'concessionaria' | 'generica' | 'catena'
--   p_preventivo               importo del preventivo IVA inclusa (NULL = solo stima, senza verdetto)
--
-- Restituisce due tabelle:
--   1. dettaglio riga per riga (ricambi e manodopera), con metodo del prezzo e intervallo D5;
--   2. riepilogo: imponibile, IVA, stima, intervallo, quota di prezzi verificati, completezza, verdetto.
--
-- Regole:
--   * tutti gli importi del database sono netti; l'IVA (settings.iva_pct) si applica una volta alla fine;
--   * ricarico solo sui ricambi, riga per riga, da markups(tipo_officina, tipo_fonte del prezzo);
--   * ore fatturate = MAX(ore stimate, settings.ore_minime);
--   * se manca il prezzo di un componente, la tariffa, le ore, o l'olio in tagliando/cambio olio:
--     completo = 0 e verdetto "dati_insufficienti" (§9.3);
--   * soglie del verdetto da settings: equo fino a +10%, sopra_media fino a +25%, oltre anomalo;
--     sotto -25% resta "equo" con avviso.
-- =====================================================================

USE equo;

DROP PROCEDURE IF EXISTS prepara_stima;
DROP PROCEDURE IF EXISTS calcola_stima;

DELIMITER $$
CREATE PROCEDURE prepara_stima(
  IN p_variante   SMALLINT UNSIGNED,
  IN p_intervento TINYINT UNSIGNED,
  IN p_qualita    VARCHAR(20),
  IN p_regione    TINYINT UNSIGNED,
  IN p_officina   VARCHAR(20)
)
BEGIN
  DECLARE v_iva, v_ore_min DECIMAL(10,4);

  SELECT CAST(valore AS DECIMAL(10,4)) / 100 INTO v_iva       FROM settings WHERE chiave = 'iva_pct';
  SELECT CAST(valore AS DECIMAL(10,4))       INTO v_ore_min   FROM settings WHERE chiave = 'ore_minime';

  -- righe dei ricambi: un componente della composizione, il codice nella qualità scelta, il prezzo corrente
  DROP TEMPORARY TABLE IF EXISTS tmp_righe;
  CREATE TEMPORARY TABLE tmp_righe (
    tipo VARCHAR(12) NOT NULL, voce VARCHAR(100) NULL, quantita DECIMAL(6,2) NULL,
    produttore VARCHAR(60) NULL, codice VARCHAR(40) NULL, generico BOOLEAN NULL,
    prezzo_netto DECIMAL(10,4) NULL, prezzo_min DECIMAL(10,4) NULL, prezzo_max DECIMAL(10,4) NULL,
    tipo_fonte VARCHAR(12) NULL, metodo VARCHAR(20) NULL, da_avvalorare BOOLEAN NULL, ricarico DECIMAL(6,4) NULL,
    ricambio_netto DECIMAL(12,4) NULL, ricarico_netto DECIMAL(12,4) NULL, totale_netto DECIMAL(12,4) NULL,
    totale_min DECIMAL(12,4) NULL, totale_max DECIMAL(12,4) NULL
  );
  INSERT INTO tmp_righe
  SELECT 'ricambio' AS tipo,
         c.descrizione AS voce,
         ic.quantita,
         s.produttore, s.codice, s.generico,
         pp.prezzo_netto, pp.prezzo_min, pp.prezzo_max, pp.tipo_fonte, pp.metodo, pp.da_avvalorare,
         m.fattore AS ricarico,
         ROUND(ic.quantita * pp.prezzo_netto, 4)                  AS ricambio_netto,
         ROUND(ic.quantita * pp.prezzo_netto * m.fattore, 4)      AS ricarico_netto,
         ROUND(ic.quantita * pp.prezzo_netto * (1 + m.fattore), 4) AS totale_netto,
         ROUND(ic.quantita * COALESCE(pp.prezzo_min, pp.prezzo_netto) * (1 + m.fattore), 4) AS totale_min,
         ROUND(ic.quantita * COALESCE(pp.prezzo_max, pp.prezzo_netto) * (1 + m.fattore), 4) AS totale_max
  FROM intervention_components ic
  JOIN components c         ON c.id = ic.componente_id
  LEFT JOIN part_qualities pq ON pq.variante_id = ic.variante_id AND pq.componente_id = ic.componente_id AND pq.qualita = p_qualita
  LEFT JOIN spare_parts s     ON s.id = pq.ricambio_id
  LEFT JOIN part_prices pp    ON pp.ricambio_id = s.id AND pp.valido_al IS NULL
  LEFT JOIN markups m         ON m.tipo_officina = p_officina AND m.tipo_fonte = pp.tipo_fonte AND m.valido_al IS NULL
  WHERE ic.variante_id = p_variante AND ic.intervento_id = p_intervento;

  -- riga della manodopera
  INSERT INTO tmp_righe (tipo, voce, quantita, prezzo_netto, metodo, totale_netto, totale_min, totale_max)
  SELECT 'manodopera',
         CONCAT('Manodopera (', lh.origine, ', tariffa ', lr.origine, ')'),
         GREATEST(lh.ore, v_ore_min),
         lr.tariffa_oraria_netta,
         CASE WHEN lh.origine = 'reale' AND lr.origine = 'reale' THEN 'rilevato' ELSE 'assunzione' END,
         ROUND(GREATEST(lh.ore, v_ore_min) * lr.tariffa_oraria_netta, 4),
         ROUND(GREATEST(lh.ore, v_ore_min) * lr.tariffa_oraria_netta, 4),
         ROUND(GREATEST(lh.ore, v_ore_min) * lr.tariffa_oraria_netta, 4)
  FROM labor_hours lh
  JOIN labor_rates lr ON lr.regione_id = p_regione AND lr.tipo_officina = p_officina AND lr.valido_al IS NULL
  WHERE lh.variante_id = p_variante AND lh.intervento_id = p_intervento;

  -- componenti senza prezzo (letti prima: una tabella temporanea non si apre due volte nella stessa query)
  SELECT GROUP_CONCAT(voce SEPARATOR ', ') INTO @mancanti FROM tmp_righe WHERE tipo = 'ricambio' AND totale_netto IS NULL;
  -- tagliando e cambio olio senza la quantità di olio non sono completi
  SET @olio_mancante = (SELECT nome IN ('Tagliando completo', 'Cambio olio e filtro') FROM interventions WHERE id = p_intervento)
                       AND NOT EXISTS (SELECT 1 FROM tmp_righe WHERE tipo = 'ricambio' AND voce = 'Olio motore');
  IF @olio_mancante THEN
    SET @mancanti = CONCAT_WS(', ', @mancanti, 'Olio motore (quantità non nota)');
  END IF;

  -- riepilogo in una riga
  DROP TEMPORARY TABLE IF EXISTS tmp_riepilogo;
  CREATE TEMPORARY TABLE tmp_riepilogo AS
    SELECT
      ROUND(SUM(CASE WHEN tipo = 'ricambio' THEN ricambio_netto ELSE 0 END), 2)  AS ricambi_netto,
      ROUND(SUM(CASE WHEN tipo = 'ricambio' THEN ricarico_netto ELSE 0 END), 2)  AS ricarico_netto,
      ROUND(SUM(CASE WHEN tipo = 'manodopera' THEN totale_netto ELSE 0 END), 2)  AS manodopera_netto,
      ROUND(SUM(totale_netto), 2)                                               AS imponibile,
      ROUND(SUM(totale_netto) * v_iva, 2)                                       AS iva,
      ROUND(SUM(totale_netto) * (1 + v_iva), 2)                                 AS stima_iva_inclusa,
      ROUND(SUM(totale_min) * (1 + v_iva), 2)                                   AS stima_da,
      ROUND(SUM(totale_max) * (1 + v_iva), 2)                                   AS stima_a,
      SUM(tipo = 'ricambio')                                                    AS ricambi_richiesti,
      SUM(tipo = 'ricambio' AND totale_netto IS NOT NULL)                       AS ricambi_con_prezzo,
      ROUND(100 * SUM(CASE WHEN tipo = 'ricambio' AND metodo = 'rilevato' THEN totale_netto ELSE 0 END)
            / NULLIF(SUM(CASE WHEN tipo = 'ricambio' THEN totale_netto ELSE 0 END), 0))  AS ricambi_verificati_pct,
      ROUND(100 * SUM(CASE WHEN tipo = 'ricambio' AND metodo = 'indicativo' THEN totale_netto ELSE 0 END)
            / NULLIF(SUM(CASE WHEN tipo = 'ricambio' THEN totale_netto ELSE 0 END), 0))  AS ricambi_indicativi_pct,
      ROUND(100 * SUM(CASE WHEN tipo = 'ricambio' AND da_avvalorare = 1 AND metodo <> 'indicativo' THEN totale_netto ELSE 0 END)
            / NULLIF(SUM(CASE WHEN tipo = 'ricambio' THEN totale_netto ELSE 0 END), 0))  AS ricambi_stimati_pct,
      MAX(tipo = 'manodopera')                                                  AS manodopera_presente,
      CASE WHEN SUM(tipo = 'ricambio') > 0
                AND SUM(tipo = 'ricambio' AND totale_netto IS NULL) = 0
                AND MAX(tipo = 'manodopera') = 1 AND NOT @olio_mancante THEN 1 ELSE 0 END AS completo
    FROM tmp_righe;
  ALTER TABLE tmp_riepilogo ADD COLUMN mancanti VARCHAR(500) NULL;
  UPDATE tmp_riepilogo SET mancanti = @mancanti;
END$$

CREATE PROCEDURE calcola_stima(
  IN p_variante   SMALLINT UNSIGNED,
  IN p_intervento TINYINT UNSIGNED,
  IN p_qualita    VARCHAR(20),
  IN p_regione    TINYINT UNSIGNED,
  IN p_officina   VARCHAR(20),
  IN p_preventivo DECIMAL(10,2)
)
BEGIN
  DECLARE v_s_sopra, v_s_anomalo, v_s_sotto DECIMAL(10,4);

  SELECT CAST(valore AS DECIMAL(10,4)) INTO v_s_sopra   FROM settings WHERE chiave = 'soglia_sopra_media';
  SELECT CAST(valore AS DECIMAL(10,4)) INTO v_s_anomalo FROM settings WHERE chiave = 'soglia_anomalo';
  SELECT CAST(valore AS DECIMAL(10,4)) INTO v_s_sotto   FROM settings WHERE chiave = 'soglia_molto_sotto';

  CALL prepara_stima(p_variante, p_intervento, p_qualita, p_regione, p_officina);

  -- 1. dettaglio
  SELECT tipo, voce, quantita, produttore, codice,
         ROUND(prezzo_netto, 2) AS prezzo_unitario_netto, metodo,
         CASE WHEN tipo = 'ricambio' THEN ROUND(ricarico * 100) END AS ricarico_pct,
         ROUND(totale_netto, 2) AS totale_netto,
         ROUND(totale_min, 2) AS da, ROUND(totale_max, 2) AS a
  FROM tmp_righe ORDER BY tipo DESC, voce;

  -- 2. riepilogo e verdetto
  SELECT r.*,
         CASE WHEN p_preventivo IS NULL THEN NULL
              ELSE ROUND((p_preventivo - r.stima_iva_inclusa) / r.stima_iva_inclusa * 100, 1) END AS scostamento_pct,
         CASE
           WHEN p_preventivo IS NULL THEN NULL
           WHEN r.completo = 0 THEN 'dati_insufficienti'
           WHEN (p_preventivo - r.stima_iva_inclusa) / r.stima_iva_inclusa <= v_s_sopra THEN 'equo'
           WHEN (p_preventivo - r.stima_iva_inclusa) / r.stima_iva_inclusa <= v_s_anomalo THEN 'sopra_media'
           ELSE 'anomalo'
         END AS esito,
         CASE WHEN p_preventivo IS NOT NULL AND r.completo = 1
                   AND (p_preventivo - r.stima_iva_inclusa) / r.stima_iva_inclusa < v_s_sotto
              THEN 'molto sotto la stima: verifica cosa è incluso' END AS avviso
  FROM tmp_riepilogo r;

  DROP TEMPORARY TABLE IF EXISTS tmp_righe;
  DROP TEMPORARY TABLE IF EXISTS tmp_riepilogo;
END$$
DELIMITER ;
