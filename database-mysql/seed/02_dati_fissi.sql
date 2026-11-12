-- =====================================================================
-- EQUO — 02 · Dati fissi
--
-- Solo dati che non vanno raccolti sul mercato: regioni, interventi,
-- componenti, parametri. Nessun prezzo, nessuna tariffa, nessun ricarico:
-- quelli arrivano dalla raccolta (nessun numero inventato).
-- Rieseguibile: svuota e ricarica queste quattro tabelle.
-- =====================================================================

USE equo;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE settings;
TRUNCATE TABLE regions;
TRUNCATE TABLE interventions;
TRUNCATE TABLE components;
SET FOREIGN_KEY_CHECKS = 1;

-- Parametri (dal design: §9.1, §9.2, §12.3, §12.5)
INSERT INTO settings (chiave, valore, descrizione) VALUES
  ('iva_pct',                '22.00', 'Aliquota IVA applicata una sola volta alla fine'),
  ('ore_minime',             '0.50',  'Minimo di ore fatturate per intervento'),
  ('soglia_sopra_media',     '0.10',  'Oltre +10% sulla stima: sopra la media'),
  ('soglia_anomalo',         '0.25',  'Oltre +25% sulla stima: anomalo'),
  ('soglia_molto_sotto',     '-0.25', 'Sotto -25%: equo con avviso "verifica cosa è incluso"'),
  ('scarto_campione',        '0.50',  'Campione oltre ±50% dalla mediana: annotato e sostituito'),
  ('min_campioni_taratura',  '5',     'Campioni minimi per pubblicare un ricarico tarato');

-- Le 20 regioni, con la fascia per le assunzioni iniziali
INSERT INTO regions (nome, area) VALUES
  ('Valle d''Aosta','nord'), ('Piemonte','nord'), ('Liguria','nord'), ('Lombardia','nord'),
  ('Trentino-Alto Adige','nord'), ('Veneto','nord'), ('Friuli-Venezia Giulia','nord'),
  ('Emilia-Romagna','nord'),
  ('Toscana','centro'), ('Umbria','centro'), ('Marche','centro'), ('Lazio','centro'),
  ('Abruzzo','sud'), ('Molise','sud'), ('Campania','sud'), ('Puglia','sud'),
  ('Basilicata','sud'), ('Calabria','sud'), ('Sicilia','sud'), ('Sardegna','sud');

-- Componenti (slot generici, design §12.5)
INSERT INTO components (descrizione, categoria, unita) VALUES
  ('Olio motore',                'olio',          'litro'),
  ('Filtro olio',                'filtri',        'pezzo'),
  ('Filtro aria',                'filtri',        'pezzo'),
  ('Filtro abitacolo',           'filtri',        'pezzo'),
  ('Filtro gasolio',             'filtri',        'pezzo'),
  ('Candela',                    'accensione',    'pezzo'),
  ('Disco freno anteriore',      'freni',         'pezzo'),
  ('Pastiglie freno anteriori',  'freni',         'set'),
  ('Kit cinghia distribuzione',  'distribuzione', 'kit'),
  ('Pompa acqua',                'distribuzione', 'pezzo'),
  ('Kit frizione',               'frizione',      'kit'),
  ('Batteria',                   'batteria',      'pezzo');

-- Interventi: 5 approvati nel design (§12.5) + pezzi singoli proposti (§12.6).
-- I pezzi singoli sono NASCOSTI dal form finché il PM non approva la lista (decisione D1).
INSERT INTO interventions (nome, descrizione, tipo, visibile_nel_form) VALUES
  ('Tagliando completo',     'Olio, filtro olio, filtro aria, filtro abitacolo; + filtro gasolio (diesel) o candele (benzina)', 'pacchetto', TRUE),
  ('Freni anteriori',        'Dischi e pastiglie anteriori',                 'pacchetto', TRUE),
  ('Cinghia distribuzione',  'Kit cinghia e pompa acqua; volano escluso',    'pacchetto', TRUE),
  ('Frizione',               'Kit frizione; volano escluso',                 'pacchetto', TRUE),
  ('Batteria',               'Sostituzione batteria, smaltimento incluso',   'singolo',   TRUE),
  ('Pastiglie freno anteriori', 'Solo pastiglie, senza dischi',              'singolo',   FALSE),
  ('Filtro abitacolo',       'Sostituzione filtro abitacolo',                'singolo',   FALSE),
  ('Filtro aria',            'Sostituzione filtro aria',                     'singolo',   FALSE),
  ('Cambio olio e filtro',   'Olio motore e filtro olio',                    'singolo',   FALSE),
  ('Pompa acqua',            'Sostituzione pompa acqua',                     'singolo',   FALSE);
