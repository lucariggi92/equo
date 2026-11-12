"""EQUO — import di dati-disponibili.xlsx nel database `equo` (task M4).

Uso, dalla cartella database-mysql:
    python import/importa_dati.py "<percorso>/dati-disponibili.xlsx"          # carica
    python import/importa_dati.py "<percorso>/dati-disponibili.xlsx" --prova  # controlla e annulla

Cosa fa:
1. legge i fogli Fonti, Varianti, Composizione, Codici, Prezzi, Ore, Tariffe, Fatture;
2. controlla ogni riga (fonte in elenco, URL/documento, data, valori ammessi);
3. applica le regole di affidabilità:
   - fonte "Basso": la riga non entra mai;
   - prezzi: basta 1 campione da fonte "Elevato", altrimenti mediana di almeno 2 campioni "Medio"
     da negozi di gruppi societari diversi (colonna Gruppo societario del foglio Fonti),
     dopo aver scartato quelli oltre ±50% dalla mediana;
4. genera un unico script SQL e lo esegue in una transazione (tutto o niente);
5. scrive il report in import/report-import.txt (righe caricate e scartate, con il motivo).

È rieseguibile: le anagrafiche si aggiornano, un prezzo uguale a quello corrente non crea righe nuove,
un prezzo diverso chiude il vecchio (valido_al) e apre il nuovo.
Nessuna dipendenza oltre a openpyxl; il database si raggiunge con il client mysql e equo_app.local.cnf.
"""
import datetime as dt
import os
import statistics
import subprocess
import sys
from collections import defaultdict

from openpyxl import load_workbook

HERE = os.path.dirname(os.path.abspath(__file__))
DB_DIR = os.path.dirname(HERE)
CNF = os.path.join(DB_DIR, 'equo_app.local.cnf')
MYSQL = r'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
SQL_OUT = os.path.join(HERE, 'ultimo-import.sql')
REPORT = os.path.join(HERE, 'report-import.txt')

SCARTO = 0.50          # settings.scarto_campione
CAMPIONI_MEDIO = 2    # decisione PM 2026-11-12 (D4): 2 negozi indipendenti

INTERVENTI = {'Tagliando completo', 'Freni anteriori', 'Cinghia distribuzione', 'Frizione', 'Batteria',
              'Pastiglie freno anteriori', 'Filtro abitacolo', 'Filtro aria', 'Cambio olio e filtro', 'Pompa acqua'}
COMPONENTI = {'Olio motore', 'Filtro olio', 'Filtro aria', 'Filtro abitacolo', 'Filtro gasolio', 'Candela',
              'Disco freno anteriore', 'Pastiglie freno anteriori', 'Kit cinghia distribuzione', 'Pompa acqua',
              'Kit frizione', 'Batteria'}
REGIONI = {"Valle d'Aosta", 'Piemonte', 'Liguria', 'Lombardia', 'Trentino-Alto Adige', 'Veneto',
           'Friuli-Venezia Giulia', 'Emilia-Romagna', 'Toscana', 'Umbria', 'Marche', 'Lazio', 'Abruzzo',
           'Molise', 'Campania', 'Puglia', 'Basilicata', 'Calabria', 'Sicilia', 'Sardegna'}
ALIMENTAZIONI = {'benzina', 'diesel', 'gpl', 'metano', 'ibrida', 'elettrica'}
QUALITA = {'originale', 'equivalente', 'economico'}
OFFICINE = {'concessionaria', 'generica', 'catena'}
TIPI_FONTE = {'listino_ufficiale', 'ecommerce', 'catalogo_produttore', 'fattura', 'officina', 'assunzione', 'altro'}

scarti = []      # (foglio, riga, motivo)
GRUPPI = {}      # fonte -> gruppo societario (negozi dello stesso gruppo valgono come uno)
caricati = defaultdict(int)


def q(v):
    """Valore Python -> letterale SQL."""
    if v is None or v == '':
        return 'NULL'
    if isinstance(v, bool):
        return '1' if v else '0'
    if isinstance(v, (int, float)):
        return repr(round(v, 4)) if isinstance(v, float) else str(v)
    if isinstance(v, (dt.date, dt.datetime)):
        return "'" + v.strftime('%Y-%m-%d') + "'"
    s = str(v).replace('\\', '\\\\').replace("'", "''")
    return "'" + s + "'"


def testo(v):
    return str(v).strip() if v not in (None, '') else ''


def numero(v):
    if v in (None, ''):
        return None
    try:
        return float(str(v).replace(',', '.')) if not isinstance(v, (int, float)) else float(v)
    except ValueError:
        return None


def data(v):
    if isinstance(v, dt.datetime):
        return v.date()
    if isinstance(v, dt.date):
        return v
    if isinstance(v, str):
        for fmt in ('%d/%m/%Y', '%Y-%m-%d'):
            try:
                return dt.datetime.strptime(v.strip(), fmt).date()
            except ValueError:
                pass
    return None


def righe(wb, foglio):
    """Righe del foglio come dizionari {intestazione: valore}, con il numero di riga Excel."""
    if foglio not in wb.sheetnames:
        return []
    ws = wb[foglio]
    head = [testo(c.value) for c in ws[1]]
    out = []
    for r in ws.iter_rows(min_row=2):
        vals = {head[i]: c.value for i, c in enumerate(r) if i < len(head) and head[i]}
        if all(v in (None, '') for v in vals.values()):
            continue
        out.append((r[0].row, vals))
    return out


def scarta(foglio, riga, motivo):
    scarti.append((foglio, riga, motivo))


def leggi_fonti(wb):
    fonti = {}
    for n, r in righe(wb, 'Fonti'):
        nome = testo(r.get('Fonte'))
        aff = testo(r.get('Affidabilità')).lower()
        tipo = testo(r.get('Tipo (tabella sources)'))
        if not nome:
            continue
        if aff not in ('basso', 'medio', 'elevato'):
            scarta('Fonti', n, f'affidabilità mancante o non valida: "{aff}"')
            continue
        if tipo not in TIPI_FONTE:
            scarta('Fonti', n, f'tipo fonte mancante o non valido: "{tipo}"')
            continue
        fonti[nome] = (aff, tipo)
        GRUPPI[nome] = testo(r.get('Gruppo societario')) or nome
    return fonti


def controlla_fonte(fonti, foglio, n, r, campo_doc='URL o documento', campo_data='Data', min_aff='medio'):
    """Restituisce (nome, affidabilità, tipo, url, data) oppure None se la riga va scartata."""
    nome = testo(r.get('Fonte'))
    if not nome:
        scarta(foglio, n, 'manca la fonte')
        return None
    if nome not in fonti:
        scarta(foglio, n, f'fonte non presente nel foglio Fonti: "{nome}"')
        return None
    aff, tipo = fonti[nome]
    doc = testo(r.get(campo_doc)) if campo_doc else ''
    d = data(r.get(campo_data))
    if campo_doc and not doc:
        scarta(foglio, n, 'manca URL o documento')
        return None
    if not d:
        scarta(foglio, n, 'manca la data')
        return None
    if aff == 'basso':
        scarta(foglio, n, 'fonte di affidabilità bassa: serve una conferma da fonte media o elevata')
        return None
    if min_aff == 'elevato' and aff != 'elevato':
        scarta(foglio, n, 'questo dato richiede una fonte elevata')
        return None
    return nome, aff, tipo, doc, d


def sql_fonte(var, nome, aff, tipo, url, d):
    """Inserisce la fonte se non esiste già (stesso riferimento, URL e data) e ne mette l'id in @var."""
    return (f"INSERT INTO sources (tipo, riferimento, affidabilita, url, data_rilevazione) "
            f"SELECT {q(tipo)}, {q(nome)}, {q(aff)}, {q(url)}, {q(d)} FROM DUAL WHERE NOT EXISTS ("
            f"SELECT 1 FROM sources WHERE riferimento = {q(nome)} AND url <=> {q(url)} AND data_rilevazione = {q(d)});\n"
            f"SET @{var} = (SELECT MIN(id) FROM sources WHERE riferimento = {q(nome)} AND url <=> {q(url)} AND data_rilevazione = {q(d)});\n")


def variante_sql(chiave):
    marca, modello, etichetta = chiave
    return (f"(SELECT v.id FROM variants v JOIN car_models m ON m.id = v.modello_id JOIN brands b ON b.id = m.marca_id "
            f"WHERE b.nome = {q(marca)} AND m.nome = {q(modello)} AND v.etichetta = {q(etichetta)})")


def main(path, prova):
    wb = load_workbook(path, data_only=True)
    fonti = leggi_fonti(wb)
    sql = ["-- Generato da import/importa_dati.py il " + dt.datetime.now().strftime('%Y-%m-%d %H:%M'),
           "USE equo;", "START TRANSACTION;"]
    oggi = dt.date.today()

    # ------------------------------------------------------------ Varianti
    varianti = {}
    for n, r in righe(wb, 'Varianti'):
        vid, marca, modello, etich = (testo(r.get(k)) for k in ('ID variante', 'Marca', 'Modello', "Etichetta per l'utente"))
        alim = testo(r.get('Alimentazione')).lower()
        distr = testo(r.get('Distribuzione')).lower() or None
        a_ini = numero(r.get('Anno inizio produzione'))
        a_fin = numero(r.get('Anno fine produzione'))
        if not all((vid, marca, modello, etich)):
            scarta('Varianti', n, 'mancano ID, marca, modello o etichetta')
            continue
        if alim not in ALIMENTAZIONI:
            scarta('Varianti', n, f'alimentazione non valida: "{alim}"')
            continue
        if distr not in (None, 'cinghia', 'catena'):
            scarta('Varianti', n, f'distribuzione non valida: "{distr}"')
            continue
        if not a_ini or (a_fin and a_fin < a_ini):
            scarta('Varianti', n, 'anni di produzione mancanti o incoerenti')
            continue
        if not controlla_fonte(fonti, 'Varianti', n, r):
            continue
        chiave = (marca, modello, etich)
        varianti[vid] = chiave
        sql.append(f"INSERT INTO brands (nome) VALUES ({q(marca)}) ON DUPLICATE KEY UPDATE nome = nome;")
        sql.append(f"INSERT INTO car_models (marca_id, nome) SELECT id, {q(modello)} FROM brands WHERE nome = {q(marca)} "
                   f"ON DUPLICATE KEY UPDATE car_models.nome = car_models.nome;")
        sql.append(
            "INSERT INTO variants (modello_id, etichetta, codice_motore, cilindrata, potenza_kw, alimentazione, distribuzione, "
            "olio_specifica, anno_produzione_inizio, anno_produzione_fine) "
            f"SELECT m.id, {q(etich)}, {q(testo(r.get('Codice motore')))}, {q(int(numero(r.get('Cilindrata cm3')) or 0) or None)}, "
            f"{q(int(numero(r.get('Potenza kW')) or 0) or None)}, {q(alim)}, {q(distr)}, {q(testo(r.get('Olio specifica')))}, "
            f"{int(a_ini)}, {q(int(a_fin) if a_fin else None)} "
            f"FROM car_models m JOIN brands b ON b.id = m.marca_id WHERE b.nome = {q(marca)} AND m.nome = {q(modello)} "
            "ON DUPLICATE KEY UPDATE codice_motore = VALUES(codice_motore), cilindrata = VALUES(cilindrata), "
            "potenza_kw = VALUES(potenza_kw), alimentazione = VALUES(alimentazione), distribuzione = VALUES(distribuzione), "
            "olio_specifica = VALUES(olio_specifica), anno_produzione_inizio = VALUES(anno_produzione_inizio), "
            "anno_produzione_fine = VALUES(anno_produzione_fine);")
        caricati['Varianti'] += 1

    def variante_ok(foglio, n, r):
        vid = testo(r.get('ID variante'))
        if vid not in varianti:
            scarta(foglio, n, f'variante "{vid}" assente o scartata nel foglio Varianti')
            return None
        return varianti[vid]

    # ------------------------------------------------------------ Composizione
    for n, r in righe(wb, 'Composizione'):
        chiave = variante_ok('Composizione', n, r)
        if not chiave:
            continue
        interv, comp, qta = testo(r.get('Intervento')), testo(r.get('Componente')), numero(r.get('Quantità'))
        if interv not in INTERVENTI or comp not in COMPONENTI:
            scarta('Composizione', n, f'intervento o componente non validi: "{interv}" / "{comp}"')
            continue
        if not qta or qta <= 0:
            scarta('Composizione', n, 'quantità mancante o non positiva')
            continue
        if not controlla_fonte(fonti, 'Composizione', n, r):
            continue
        sql.append(
            "INSERT INTO intervention_components (variante_id, intervento_id, componente_id, quantita) "
            f"SELECT {variante_sql(chiave)}, i.id, c.id, {qta} FROM interventions i, components c "
            f"WHERE i.nome = {q(interv)} AND c.descrizione = {q(comp)} "
            "ON DUPLICATE KEY UPDATE quantita = VALUES(quantita);")
        caricati['Composizione'] += 1

    # ------------------------------------------------------------ Codici
    codici = set()
    for n, r in righe(wb, 'Codici'):
        chiave = variante_ok('Codici', n, r)
        if not chiave:
            continue
        comp, qual = testo(r.get('Componente')), testo(r.get('Qualità')).lower()
        prod, cod = testo(r.get('Produttore')), testo(r.get('Codice'))
        if comp not in COMPONENTI or qual not in QUALITA:
            scarta('Codici', n, f'componente o qualità non validi: "{comp}" / "{qual}"')
            continue
        if not prod or not cod:
            scarta('Codici', n, 'mancano produttore o codice')
            continue
        if not controlla_fonte(fonti, 'Codici', n, r):
            continue
        codici.add((prod, cod))
        desc = testo(r.get('Codici OE collegati'))
        sql.append(f"INSERT INTO spare_parts (codice, produttore, descrizione) VALUES ({q(cod)}, {q(prod)}, "
                   f"{q(('OE: ' + desc) if desc else None)}) ON DUPLICATE KEY UPDATE descrizione = VALUES(descrizione);")
        sql.append(
            "INSERT INTO part_qualities (variante_id, componente_id, qualita, ricambio_id) "
            f"SELECT {variante_sql(chiave)}, c.id, {q(qual)}, s.id FROM components c, spare_parts s "
            f"WHERE c.descrizione = {q(comp)} AND s.produttore = {q(prod)} AND s.codice = {q(cod)} "
            "ON DUPLICATE KEY UPDATE ricambio_id = VALUES(ricambio_id);")
        caricati['Codici'] += 1

    # ------------------------------------------------------------ Prezzi
    gruppi = defaultdict(list)
    for n, r in righe(wb, 'Prezzi'):
        prod, cod = testo(r.get('Produttore')), testo(r.get('Codice'))
        if (prod, cod) not in codici:
            scarta('Prezzi', n, f'codice {prod} {cod} assente o scartato nel foglio Codici')
            continue
        f = controlla_fonte(fonti, 'Prezzi', n, r)
        if not f:
            continue
        p = numero(r.get('Prezzo esposto €'))
        iva_incl = testo(r.get('IVA inclusa')).lower()
        iva_pct = numero(r.get('IVA della fonte'))
        iva_pct = 0.22 if iva_pct is None else (iva_pct / 100 if iva_pct > 1 else iva_pct)
        if not p or p <= 0:
            scarta('Prezzi', n, 'prezzo mancante o non positivo')
            continue
        if iva_incl not in ('sì', 'si', 'no'):
            scarta('Prezzi', n, 'indicare se il prezzo è IVA inclusa (Sì/No)')
            continue
        netto = round(p / (1 + iva_pct), 4) if iva_incl != 'no' else round(p, 4)
        gruppi[(prod, cod)].append(dict(riga=n, fonte=f, lordo=p, iva=iva_incl != 'no', iva_pct=iva_pct, netto=netto))

    esiti_prezzi = []
    for (prod, cod), camp in gruppi.items():
        elevati = [c for c in camp if c['fonte'][1] == 'elevato']
        medi = [c for c in camp if c['fonte'][1] == 'medio']
        # un solo campione per gruppo societario: tre negozi dello stesso gruppo non sono tre prezzi indipendenti
        visti, doppi = set(), []
        for c in list(medi):
            g = GRUPPI.get(c['fonte'][0], c['fonte'][0])
            if g in visti:
                medi.remove(c)
                doppi.append(c)
            else:
                visti.add(g)
        for c in doppi:
            scarta('Prezzi', c['riga'], f'stesso gruppo societario ({GRUPPI.get(c["fonte"][0])}) di un altro campione: conta una volta sola')
        scartati = []
        if elevati:
            usati = elevati
            tipo_prezzo = 'ufficiale' if any(c['fonte'][2] == 'listino_ufficiale' for c in elevati) else 'media'
        else:
            if len(medi) < CAMPIONI_MEDIO:
                esiti_prezzi.append(f'{prod} {cod}: NON caricato, {len(medi)} negozi indipendenti su {CAMPIONI_MEDIO}')
                for c in camp:
                    if c not in doppi:
                        scarta('Prezzi', c['riga'], f'{prod} {cod}: mancano {CAMPIONI_MEDIO - len(medi)} campioni da negozi indipendenti')
                continue
            m = statistics.median(c['netto'] for c in medi)
            usati = [c for c in medi if abs(c['netto'] - m) / m <= SCARTO]
            scartati = [c for c in medi if c not in usati]
            if len(usati) < CAMPIONI_MEDIO:
                esiti_prezzi.append(f'{prod} {cod}: NON caricato, dopo lo scarto oltre ±50% restano {len(usati)} campioni')
                for c in camp:
                    scarta('Prezzi', c['riga'], f'gruppo {prod} {cod}: dopo lo scarto restano meno di {CAMPIONI_MEDIO} campioni')
                continue
            tipo_prezzo = 'media'
        valori = [c['netto'] for c in usati]
        prezzo = round(statistics.median(valori), 4)
        d_ril = max(c['fonte'][4] for c in usati)
        n_c = len(usati)
        sql.append(f"-- Prezzo {prod} {cod}")
        sql.append(f"SET @rid = (SELECT id FROM spare_parts WHERE produttore = {q(prod)} AND codice = {q(cod)});")
        sql.append("SET @cur = (SELECT id FROM part_prices WHERE ricambio_id = @rid AND valido_al IS NULL);")
        sql.append(f"SET @same = (SELECT COUNT(*) FROM part_prices WHERE id = @cur AND prezzo_netto = {prezzo} "
                   f"AND n_campioni = {n_c} AND tipo_fonte = {q(tipo_prezzo)});")
        sql.append(f"UPDATE part_prices SET valido_al = GREATEST(valido_dal, {q(oggi)}) WHERE id = @cur AND @same = 0;")
        sql.append(
            "INSERT INTO part_prices (ricambio_id, prezzo_netto, tipo_fonte, n_campioni, prezzo_min, prezzo_max, "
            "data_rilevazione, valido_dal) "
            f"SELECT @rid, {prezzo}, {q(tipo_prezzo)}, {n_c}, {min(valori)}, {max(valori)}, {q(d_ril)}, {q(oggi)} "
            "FROM DUAL WHERE @same = 0;")
        sql.append("SET @pid = (SELECT id FROM part_prices WHERE ricambio_id = @rid AND valido_al IS NULL);")
        for c in usati + scartati:
            nome, aff, tipo, url, d = c['fonte']
            sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
            sql.append(
                "INSERT INTO price_samples (prezzo_id, fonte_id, prezzo_fonte, iva_inclusa, iva_fonte_pct, prezzo_netto, scartato, note) "
                f"SELECT @pid, @fid, {c['lordo']}, {q(c['iva'])}, {round(c['iva_pct'] * 100, 2)}, {c['netto']}, "
                f"{q(c in scartati)}, {q('oltre ±50% dalla mediana' if c in scartati else None)} FROM DUAL WHERE @same = 0;")
        esiti_prezzi.append(f'{prod} {cod}: caricato {prezzo:.2f} € netti ({tipo_prezzo}, {n_c} campioni'
                            + (f', {len(scartati)} scartati' if scartati else '') + ')')
        caricati['Prezzi (codici)'] += 1

    # ------------------------------------------------------------ Ore
    for n, r in righe(wb, 'Ore'):
        chiave = variante_ok('Ore', n, r)
        if not chiave:
            continue
        interv, ore = testo(r.get('Intervento')), numero(r.get('Ore'))
        if interv not in INTERVENTI or not ore or ore <= 0:
            scarta('Ore', n, 'intervento non valido o ore mancanti')
            continue
        f = controlla_fonte(fonti, 'Ore', n, r)
        if not f:
            continue
        nome, aff, tipo, url, d = f
        origine = 'reale' if tipo in ('fattura', 'officina') else 'assunzione'
        sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
        sql.append(
            "INSERT INTO labor_hours (variante_id, intervento_id, ore, origine, fonte_id, note) "
            f"SELECT {variante_sql(chiave)}, i.id, {ore}, {q(origine)}, @fid, {q(testo(r.get('Note')))} FROM interventions i "
            f"WHERE i.nome = {q(interv)} ON DUPLICATE KEY UPDATE ore = VALUES(ore), origine = VALUES(origine), "
            "fonte_id = VALUES(fonte_id), note = VALUES(note);")
        caricati['Ore'] += 1

    # ------------------------------------------------------------ Tariffe
    tariffe = defaultdict(list)
    for n, r in righe(wb, 'Tariffe'):
        reg, off, t = testo(r.get('Regione')), testo(r.get('Tipo officina')).lower(), numero(r.get('Tariffa oraria netta €'))
        if reg not in REGIONI or off not in OFFICINE or not t or t <= 0:
            scarta('Tariffe', n, 'regione, tipo officina o tariffa non validi')
            continue
        f = controlla_fonte(fonti, 'Tariffe', n, r)
        if f:
            tariffe[(reg, off)].append((t, f))
    for (reg, off), vals in tariffe.items():
        valore = round(statistics.median(v for v, _ in vals), 2)
        ultima = max(vals, key=lambda x: x[1][4])[1]
        nome, aff, tipo, url, d = ultima
        sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
        sql.append(f"SET @reg = (SELECT id FROM regions WHERE nome = {q(reg)});")
        sql.append(f"SET @cur = (SELECT id FROM labor_rates WHERE regione_id = @reg AND tipo_officina = {q(off)} AND valido_al IS NULL);")
        sql.append(f"SET @same = (SELECT COUNT(*) FROM labor_rates WHERE id = @cur AND tariffa_oraria_netta = {valore} "
                   f"AND n_campioni = {len(vals)});")
        sql.append(f"UPDATE labor_rates SET valido_al = GREATEST(valido_dal, {q(oggi)}) WHERE id = @cur AND @same = 0;")
        sql.append(
            "INSERT INTO labor_rates (regione_id, tipo_officina, tariffa_oraria_netta, origine, n_campioni, data_rilevazione, "
            f"valido_dal, fonte_id) SELECT @reg, {q(off)}, {valore}, 'reale', {len(vals)}, {q(d)}, {q(oggi)}, @fid "
            "FROM DUAL WHERE @same = 0;")
        caricati['Tariffe (regione e officina)'] += 1

    # ------------------------------------------------------------ Fatture
    prezzi_ok = {k for k in gruppi if any(e.startswith(f'{k[0]} {k[1]}: caricato') for e in esiti_prezzi)}
    for n, r in righe(wb, 'Fatture'):
        num, reg, off = testo(r.get('N. fattura (oscurata)')), testo(r.get('Regione')), testo(r.get('Tipo officina')).lower()
        prod, cod, p = testo(r.get('Produttore')), testo(r.get('Codice')), numero(r.get('Prezzo in fattura netto €'))
        if not num or reg not in REGIONI or off not in OFFICINE or not p or p <= 0:
            scarta('Fatture', n, 'numero fattura, regione, tipo officina o prezzo non validi')
            continue
        if (prod, cod) not in prezzi_ok:
            scarta('Fatture', n, f'manca il prezzo di riferimento corrente di {prod} {cod} (foglio Prezzi)')
            continue
        f = controlla_fonte(fonti, 'Fatture', n, r, campo_doc=None, campo_data='Data fattura', min_aff='elevato')
        if not f:
            continue
        nome, aff, tipo, _, d = f
        sql.append(sql_fonte('fid', f'{nome} — fattura {num}', aff, tipo, None, d).rstrip('\n'))
        sql.append(
            "INSERT INTO calibrations (fonte_id, ricambio_id, tipo_officina, regione_id, prezzo_fattura_netto, "
            "prezzo_riferimento_netto, tipo_fonte_riferimento, data_fattura) "
            f"SELECT @fid, s.id, {q(off)}, g.id, {p}, pp.prezzo_netto, pp.tipo_fonte, {q(d)} "
            "FROM spare_parts s JOIN part_prices pp ON pp.ricambio_id = s.id AND pp.valido_al IS NULL "
            f"JOIN regions g ON g.nome = {q(reg)} WHERE s.produttore = {q(prod)} AND s.codice = {q(cod)} "
            "AND NOT EXISTS (SELECT 1 FROM calibrations c WHERE c.fonte_id = @fid AND c.ricambio_id = s.id);")
        caricati['Fatture'] += 1

    # ------------------------------------------------------------ esecuzione
    sql.append("SELECT 'variants' AS tabella, COUNT(*) AS righe FROM variants UNION ALL "
               "SELECT 'intervention_components', COUNT(*) FROM intervention_components UNION ALL "
               "SELECT 'spare_parts', COUNT(*) FROM spare_parts UNION ALL "
               "SELECT 'part_qualities', COUNT(*) FROM part_qualities UNION ALL "
               "SELECT 'part_prices (correnti)', COUNT(*) FROM part_prices WHERE valido_al IS NULL UNION ALL "
               "SELECT 'price_samples', COUNT(*) FROM price_samples UNION ALL "
               "SELECT 'sources', COUNT(*) FROM sources UNION ALL "
               "SELECT 'labor_hours', COUNT(*) FROM labor_hours UNION ALL "
               "SELECT 'labor_rates (correnti)', COUNT(*) FROM labor_rates WHERE valido_al IS NULL UNION ALL "
               "SELECT 'calibrations', COUNT(*) FROM calibrations;")
    sql.append("ROLLBACK;" if prova else "COMMIT;")
    with open(SQL_OUT, 'w', encoding='utf-8') as fh:
        fh.write('\n'.join(sql) + '\n')

    res = subprocess.run([MYSQL, f'--defaults-extra-file={CNF}', '--default-character-set=utf8mb4', '-t',
                          '-e', f"source {SQL_OUT.replace(os.sep, '/')}"],
                         capture_output=True, text=True, encoding='utf-8')
    ok = res.returncode == 0

    lines = [f"Import di {path}", f"Data: {dt.datetime.now():%Y-%m-%d %H:%M}",
             "Modalità: PROVA (nessuna modifica salvata)" if prova else "Modalità: CARICAMENTO",
             f"Esito database: {'OK' if ok else 'ERRORE, nessuna modifica salvata'}", "",
             "Righe accettate per foglio:"]
    lines += [f"  {k}: {v}" for k, v in caricati.items()] or ["  nessuna"]
    lines += ["", "Prezzi:"] + ([f"  {e}" for e in esiti_prezzi] or ["  nessun prezzo nel file"])
    lines += ["", f"Righe scartate: {len(scarti)}"]
    lines += [f"  {f} riga {n}: {m}" for f, n, m in sorted(scarti)]
    lines += ["", "Database dopo l'import:" if not prova else "Database come sarebbe dopo l'import:", res.stdout.strip()]
    if not ok:
        lines += ["", "Errore MySQL:", res.stderr.strip()]
    report = '\n'.join(lines)
    with open(REPORT, 'w', encoding='utf-8') as fh:
        fh.write(report + '\n')
    print(report)
    return 0 if ok else 1


if __name__ == '__main__':
    sys.stdout.reconfigure(encoding='utf-8')
    if len(sys.argv) < 2:
        print(__doc__)
        sys.exit(2)
    sys.exit(main(sys.argv[1], '--prova' in sys.argv[2:]))
