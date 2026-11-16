-- =====================================================================
-- EQUO — M7 · Controlli di copertura
--
-- Crea la procedura controlli_copertura. Uso, dopo `source queries/controlli_copertura.sql`:
--
--   CALL controlli_copertura(180);   -- 180 = giorni oltre i quali un prezzo è "vecchio"
--
-- Restituisce sei tabelle:
--   1. per variante e qualità: interventi calcolabili su 10, ricambi con prezzo, quota verificata/indicativa/stimata;
--   2. interventi non calcolabili, con il motivo (manca la composizione, mancano ore, manca un prezzo);
--   3. ricambi senza prezzo corrente;
--   4. prezzi vecchi (rilevazione più vecchia della soglia) e storico chiuso senza prezzo corrente;
--   5. tariffe orarie e ricarichi mancanti (60 tariffe e 6 ricarichi attesi);
--   6. dati tecnici mancanti nelle varianti (olio, distribuzione, anno di fine produzione).
-- Un intervento è calcolabile in una qualità quando ha composizione, ore, un prezzo per ogni componente
-- e, per tagliando e cambio olio, la quantità di olio
-- (con la stessa regola della procedura calcola_stima).
-- =====================================================================

USE equo;

DROP PROCEDURE IF EXISTS controlli_copertura;

DELIMITER $$
CREATE PROCEDURE controlli_copertura(IN p_giorni SMALLINT UNSIGNED)
BEGIN
  -- stato di ogni combinazione variante × intervento × qualità
  DROP TEMPORARY TABLE IF EXISTS tmp_cop;
  CREATE TEMPORARY TABLE tmp_cop (
    variante_id SMALLINT UNSIGNED, intervento_id TINYINT UNSIGNED, qualita VARCHAR(12),
    componenti INT, con_prezzo INT, valore DECIMAL(12,4), valore_rilevato DECIMAL(12,4),
    valore_indicativo DECIMAL(12,4), ore_presenti TINYINT, olio_mancante TINYINT
  );
  INSERT INTO tmp_cop
  SELECT v.id, i.id, q.qualita,
         COUNT(ic.componente_id),
         SUM(pp.id IS NOT NULL),
         SUM(ic.quantita * pp.prezzo_netto),
         SUM(CASE WHEN pp.metodo = 'rilevato' THEN ic.quantita * pp.prezzo_netto ELSE 0 END),
         SUM(CASE WHEN pp.metodo = 'indicativo' THEN ic.quantita * pp.prezzo_netto ELSE 0 END),
         MAX(lh.ore IS NOT NULL),
         (i.nome IN ('Tagliando completo', 'Cambio olio e filtro')
          AND NOT EXISTS (SELECT 1 FROM intervention_components x JOIN components c2 ON c2.id = x.componente_id
                          WHERE x.variante_id = v.id AND x.intervento_id = i.id AND c2.descrizione = 'Olio motore'))
  FROM variants v
  CROSS JOIN interventions i
  CROSS JOIN (SELECT 'originale' AS qualita UNION ALL SELECT 'equivalente' UNION ALL SELECT 'economico') q
  LEFT JOIN intervention_components ic ON ic.variante_id = v.id AND ic.intervento_id = i.id
  LEFT JOIN part_qualities pq ON pq.variante_id = v.id AND pq.componente_id = ic.componente_id AND pq.qualita = q.qualita
  LEFT JOIN part_prices pp    ON pp.ricambio_id = pq.ricambio_id AND pp.valido_al IS NULL
  LEFT JOIN labor_hours lh    ON lh.variante_id = v.id AND lh.intervento_id = i.id
  WHERE i.visibile_nel_form
  GROUP BY v.id, i.id, q.qualita;

  -- 1. riepilogo per variante e qualità
  SELECT CONCAT(b.nome, ' ', m.nome) AS modello, v.etichetta AS variante, c.qualita,
         SUM(c.componenti > 0 AND c.componenti = c.con_prezzo AND c.ore_presenti = 1 AND c.olio_mancante = 0) AS interventi_calcolabili,
         COUNT(*) AS interventi_totali,
         ROUND(100 * SUM(c.valore_rilevato) / NULLIF(SUM(c.valore), 0)) AS valore_verificato_pct,
         ROUND(100 * SUM(c.valore_indicativo) / NULLIF(SUM(c.valore), 0)) AS valore_indicativo_pct,
         ROUND(100 - 100 * (SUM(c.valore_rilevato) + SUM(c.valore_indicativo)) / NULLIF(SUM(c.valore), 0)) AS valore_stimato_pct,
         m.info_completed
  FROM tmp_cop c
  JOIN variants v   ON v.id = c.variante_id
  JOIN car_models m ON m.id = v.modello_id
  JOIN brands b     ON b.id = m.marca_id
  GROUP BY c.variante_id, c.qualita
  ORDER BY v.id, FIELD(c.qualita, 'originale', 'equivalente', 'economico');

  -- 2. interventi non calcolabili e motivo
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante, i.nome AS intervento, c.qualita,
         CASE WHEN c.componenti = 0 THEN 'manca la composizione'
              WHEN c.olio_mancante = 1 THEN 'manca la quantità di olio'
              WHEN c.ore_presenti = 0 THEN 'mancano le ore'
              ELSE CONCAT('mancano ', c.componenti - c.con_prezzo, ' prezzi su ', c.componenti) END AS motivo
  FROM tmp_cop c
  JOIN variants v      ON v.id = c.variante_id
  JOIN car_models m    ON m.id = v.modello_id
  JOIN interventions i ON i.id = c.intervento_id
  WHERE NOT (c.componenti > 0 AND c.componenti = c.con_prezzo AND c.ore_presenti = 1 AND c.olio_mancante = 0)
  ORDER BY v.id, i.id, FIELD(c.qualita, 'originale', 'equivalente', 'economico');

  -- 3. ricambi presenti nella composizione senza codice o senza prezzo corrente
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante, co.descrizione AS componente, q.qualita,
         COALESCE(CONCAT(s.produttore, ' ', s.codice), '— nessun codice —') AS ricambio
  FROM (SELECT DISTINCT variante_id, componente_id FROM intervention_components) ic
  CROSS JOIN (SELECT 'originale' AS qualita UNION ALL SELECT 'equivalente' UNION ALL SELECT 'economico') q
  JOIN variants v       ON v.id = ic.variante_id
  JOIN car_models m     ON m.id = v.modello_id
  JOIN components co    ON co.id = ic.componente_id
  LEFT JOIN part_qualities pq ON pq.variante_id = ic.variante_id AND pq.componente_id = ic.componente_id AND pq.qualita = q.qualita
  LEFT JOIN spare_parts s     ON s.id = pq.ricambio_id
  LEFT JOIN part_prices pp    ON pp.ricambio_id = s.id AND pp.valido_al IS NULL
  WHERE pp.id IS NULL
  ORDER BY v.id, co.id, FIELD(q.qualita, 'originale', 'equivalente', 'economico');

  -- 4. prezzi vecchi e ricambi con solo storico chiuso
  SELECT CONCAT(s.produttore, ' ', s.codice) AS ricambio, pp.metodo, pp.data_rilevazione,
         DATEDIFF(CURDATE(), pp.data_rilevazione) AS giorni, 'prezzo corrente più vecchio della soglia' AS problema
  FROM part_prices pp JOIN spare_parts s ON s.id = pp.ricambio_id
  WHERE pp.valido_al IS NULL AND pp.data_rilevazione < CURDATE() - INTERVAL p_giorni DAY
  UNION ALL
  SELECT CONCAT(s.produttore, ' ', s.codice), MAX(pp.metodo), MAX(pp.data_rilevazione),
         DATEDIFF(CURDATE(), MAX(pp.data_rilevazione)), 'solo prezzi scaduti, nessun prezzo corrente'
  FROM part_prices pp JOIN spare_parts s ON s.id = pp.ricambio_id
  WHERE NOT EXISTS (SELECT 1 FROM part_prices x WHERE x.ricambio_id = pp.ricambio_id AND x.valido_al IS NULL)
  GROUP BY s.id;

  -- 5. tariffe e ricarichi mancanti
  SELECT 'tariffa oraria' AS dato, CONCAT(r.nome, ' · ', t.tipo) AS chiave
  FROM regions r
  CROSS JOIN (SELECT 'concessionaria' AS tipo UNION ALL SELECT 'generica' UNION ALL SELECT 'catena') t
  WHERE NOT EXISTS (SELECT 1 FROM labor_rates lr WHERE lr.regione_id = r.id AND lr.tipo_officina = t.tipo AND lr.valido_al IS NULL)
  UNION ALL
  SELECT 'ricarico', CONCAT(t.tipo, ' · ', f.tipo_fonte)
  FROM (SELECT 'concessionaria' AS tipo UNION ALL SELECT 'generica' UNION ALL SELECT 'catena') t
  CROSS JOIN (SELECT 'media' AS tipo_fonte UNION ALL SELECT 'ufficiale') f
  WHERE NOT EXISTS (SELECT 1 FROM markups mk WHERE mk.tipo_officina = t.tipo AND mk.tipo_fonte = f.tipo_fonte AND mk.valido_al IS NULL);

  -- 6. dati tecnici mancanti nelle varianti
  SELECT CONCAT(m.nome, ' ', v.etichetta) AS variante,
         CONCAT_WS(', ',
           IF(NOT EXISTS (SELECT 1 FROM intervention_components x JOIN components c ON c.id = x.componente_id
                          WHERE x.variante_id = v.id AND c.descrizione = 'Olio motore'), 'quantità olio', NULL),
           IF(v.olio_specifica IS NULL, 'specifica olio', NULL),
           IF(v.distribuzione IS NULL, 'cinghia o catena', NULL),
           IF(v.anno_produzione_fine IS NULL, 'anno fine produzione', NULL)) AS mancano
  FROM variants v JOIN car_models m ON m.id = v.modello_id
  HAVING mancano <> ''
  ORDER BY v.id;

  DROP TEMPORARY TABLE IF EXISTS tmp_cop;
END$$
DELIMITER ;
