"""EQUO — import di dati-disponibili.xlsx nel database `equo` (task M4).

Uso, dalla cartella database-mysql:
    python import/importa_dati.py "<percorso>/dati-disponibili.xlsx"          # carica
    python import/importa_dati.py "<percorso>/dati-disponibili.xlsx" --prova  # controlla e annulla

Cosa fa:
1. legge i fogli Fonti, Varianti, Composizione, Codici, Prezzi, Riferimenti, Ore, Tariffe, Ricarichi, Fatture;
2. controlla ogni riga (fonte in elenco, URL/documento, data, valori ammessi);
3. applica le regole di affidabilità:
   - fonte "Basso": la riga non entra mai;
   - prezzi: basta 1 campione da fonte "Elevato", altrimenti mediana di almeno 2 campioni "Medio"
     da negozi di gruppi societari diversi (colonna Gruppo societario del foglio Fonti),
     dopo aver scartato quelli oltre ±50% dalla mediana;
4. D5: dove manca il prezzo rilevato lo stima con una scala dichiarata (indicativo, rapporto tra qualità,
   mediana di marca, mediana di categoria), marcata da_avvalorare; per i pezzi senza codice crea un ricambio
   generico di categoria; misura l'affidabilità delle stime con un backtest sui prezzi reali;
5. genera un unico script SQL e lo esegue in una transazione (tutto o niente);
6. scrive il report in import/report-import.txt (righe caricate e scartate, con il motivo).

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
MIN_STAT = 3          # campioni minimi per una mediana statistica (D5)
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
    comp_var = defaultdict(set)          # variante -> componenti che usa
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
        comp_var[chiave].add(comp)
        caricati['Composizione'] += 1

    # ------------------------------------------------------------ Codici
    codici = set()
    code_meta = {}                       # (produttore, codice) -> (componente, qualità)
    sott = {}                            # (produttore, codice) -> sottotipo (D5)
    slots = defaultdict(dict)            # (variante, componente) -> {qualità: (produttore, codice)}
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
        code_meta.setdefault((prod, cod), (comp, qual))
        sott[(prod, cod)] = testo(r.get('Sottotipo'))
        slots[(chiave, comp)][qual] = (prod, cod)
        desc = testo(r.get('Codici OE collegati'))
        sql.append(f"INSERT INTO spare_parts (codice, produttore, descrizione, sottotipo) VALUES ({q(cod)}, {q(prod)}, "
                   f"{q(('OE: ' + desc) if desc else None)}, {q(testo(r.get('Sottotipo')))}) "
                   "ON DUPLICATE KEY UPDATE descrizione = VALUES(descrizione), sottotipo = VALUES(sottotipo);")
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
    prezzi_reali = {}                    # (produttore, codice) -> (prezzo netto, metodo)

    def scrivi_prezzo(prod, cod, prezzo, tipo_prezzo, metodo, n_c, pmin, pmax, d_ril, nota, usati=(), scartati=()):
        prezzo = round(prezzo, 4)
        pmin = round(min(pmin, prezzo), 4)
        pmax = round(max(pmax, prezzo), 4)
        stimato = metodo != 'rilevato'
        n_c = max(1, min(int(n_c), 255))
        sql.append(f"-- Prezzo {prod} {cod} ({metodo})")
        sql.append(f"SET @rid = (SELECT id FROM spare_parts WHERE produttore = {q(prod)} AND codice = {q(cod)});")
        sql.append("SET @cur = (SELECT id FROM part_prices WHERE ricambio_id = @rid AND valido_al IS NULL);")
        sql.append(f"SET @same = (SELECT COUNT(*) FROM part_prices WHERE id = @cur AND prezzo_netto = {prezzo} "
                   f"AND n_campioni = {n_c} AND tipo_fonte = {q(tipo_prezzo)} AND metodo = {q(metodo)});")
        sql.append(f"UPDATE part_prices SET valido_al = GREATEST(valido_dal, {q(oggi)}) WHERE id = @cur AND @same = 0;")
        sql.append(
            "INSERT INTO part_prices (ricambio_id, prezzo_netto, tipo_fonte, metodo, da_avvalorare, nota_stima, n_campioni, "
            "prezzo_min, prezzo_max, data_rilevazione, valido_dal) "
            f"SELECT @rid, {prezzo}, {q(tipo_prezzo)}, {q(metodo)}, {q(stimato)}, {q(nota[:255] if nota else None)}, {n_c}, "
            f"{pmin}, {pmax}, {q(d_ril)}, {q(oggi)} FROM DUAL WHERE @same = 0;")
        sql.append("SET @pid = (SELECT id FROM part_prices WHERE ricambio_id = @rid AND valido_al IS NULL);")
        for c in list(usati) + list(scartati):
            nome, aff, tipo, url, d = c['fonte']
            sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
            sql.append(
                "INSERT INTO price_samples (prezzo_id, fonte_id, prezzo_fonte, iva_inclusa, iva_fonte_pct, prezzo_netto, scartato, note) "
                f"SELECT @pid, @fid, {c['lordo']}, {q(c['iva'])}, {round(c['iva_pct'] * 100, 2)}, {c['netto']}, "
                f"{q(c in scartati)}, {q('oltre ±50% dalla mediana' if c in scartati else None)} FROM DUAL WHERE @same = 0;")
        caricati['Prezzi (codici)'] += 1

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
            usati, metodo = elevati, 'rilevato'
            tipo_prezzo = 'ufficiale' if any(c['fonte'][2] == 'listino_ufficiale' for c in elevati) else 'media'
        elif len(medi) >= CAMPIONI_MEDIO:
            m = statistics.median(c['netto'] for c in medi)
            usati = [c for c in medi if abs(c['netto'] - m) / m <= SCARTO]
            scartati = [c for c in medi if c not in usati]
            metodo = 'rilevato' if len(usati) >= CAMPIONI_MEDIO else 'indicativo'
            tipo_prezzo = 'media'
        elif len(medi) == 1:
            usati, metodo, tipo_prezzo = medi, 'indicativo', 'media'
        else:
            continue
        valori = [c['netto'] for c in usati]
        prezzo = statistics.median(valori)
        nota = None if metodo == 'rilevato' else 'D5 livello 2: un solo negozio indipendente, da confermare con un secondo negozio'
        scrivi_prezzo(prod, cod, prezzo, tipo_prezzo, metodo, len(usati), min(valori), max(valori),
                      max(c['fonte'][4] for c in usati), nota, usati, scartati)
        prezzi_reali[(prod, cod)] = (round(prezzo, 4), metodo)
        esiti_prezzi.append(f'{prod} {cod}: {metodo} {prezzo:.2f} € netti ({len(usati)} negozi indipendenti'
                            + (f', {len(scartati)} scartati' if scartati else '') + ')')

    # ------------------------------------------------------------ Riferimenti (base statistica D5)
    riferimenti = {}                     # (produttore, codice) -> (componente, qualità, netto)
    for n, r in righe(wb, 'Riferimenti'):
        comp, qual = testo(r.get('Componente')), testo(r.get('Qualità')).lower()
        prod, cod = testo(r.get('Produttore')), testo(r.get('Codice'))
        p = numero(r.get('Prezzo esposto €'))
        iva_incl = testo(r.get('IVA inclusa')).lower()
        iva_pct = numero(r.get('IVA della fonte'))
        iva_pct = 0.22 if iva_pct is None else (iva_pct / 100 if iva_pct > 1 else iva_pct)
        if comp not in COMPONENTI or qual not in QUALITA or not prod or not cod or not p or p <= 0:
            scarta('Riferimenti', n, 'componente, qualità, produttore, codice o prezzo non validi')
            continue
        f = controlla_fonte(fonti, 'Riferimenti', n, r)
        if not f:
            continue
        netto = round(p / (1 + iva_pct), 4) if iva_incl != 'no' else round(p, 4)
        if (prod, cod) in riferimenti:
            continue
        riferimenti[(prod, cod)] = (comp, qual, netto)
        sott.setdefault((prod, cod), testo(r.get('Sottotipo')))
        nome, aff, tipo, url, d = f
        sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
        sql.append("INSERT INTO reference_prices (componente_id, qualita, produttore, codice, sottotipo, prezzo_netto, fonte_id, data_rilevazione) "
                   f"SELECT c.id, {q(qual)}, {q(prod)}, {q(cod)}, {q(testo(r.get('Sottotipo')))}, {netto}, @fid, {q(d)} FROM components c WHERE c.descrizione = {q(comp)} "
                   "ON DUPLICATE KEY UPDATE prezzo_netto = VALUES(prezzo_netto), data_rilevazione = VALUES(data_rilevazione);")
        caricati['Riferimenti'] += 1

    # base dati statistica: riferimenti + prezzi reali dei nostri codici (un valore per codice)
    base = dict(riferimenti)
    for code, (prezzo, _) in prezzi_reali.items():
        if code not in base and code in code_meta:
            base[code] = (code_meta[code][0], code_meta[code][1], prezzo)

    def quartili(vals):
        vals = sorted(vals)
        if len(vals) < 2:
            return vals[0], vals[0], vals[0]
        q1, med, q3 = statistics.quantiles(vals, n=4, method='inclusive')
        return q1, statistics.median(vals), q3

    def valori(escludi=None, comp=None, qual=None, prod=None, sottotipo=None):
        return [v for k, (c, ql, v) in base.items() if k != escludi and c == comp
                and (qual is None or ql == qual) and (prod is None or k[0] == prod)
                and (not sottotipo or sott.get(k, '') == sottotipo)]

    def rapporto(comp, q_da, q_a, escludi=None):
        """Rapporto mediano tra due qualità: prima nella stessa categoria, altrimenti su tutte le categorie."""
        a, b = valori(escludi, comp, q_a), valori(escludi, comp, q_da)
        if len(a) >= MIN_STAT and len(b) >= MIN_STAT:
            return statistics.median(a) / statistics.median(b), f'categoria {comp}'
        rap = []
        for c in COMPONENTI:
            a, b = valori(escludi, c, q_a), valori(escludi, c, q_da)
            if len(a) >= MIN_STAT and len(b) >= MIN_STAT:
                rap.append(statistics.median(a) / statistics.median(b))
        if rap:
            return statistics.median(rap), f'mediana di {len(rap)} categorie'
        return None, None

    def stima(code, comp, qual, chiavi_slot, escludi=None):
        """Scala D5 dal livello 3 al 5. Restituisce (metodo, prezzo, min, max, n, nota) oppure None."""
        # livelli 4 e 5, prima nello stesso sottotipo (es. disco ventilato), poi nella categoria intera
        prod, st_ = code[0], sott.get(code, '')
        for sotto in ([st_, None] if st_ else [None]):
            etichetta = f'{comp} {sotto}' if sotto else comp
            v = valori(escludi or code, comp, None, prod, sotto)
            if prod != 'GENERICO' and len(v) >= MIN_STAT:
                q1, med, q3 = quartili(v)
                return ('mediana_marca', med, q1, q3, len(v), f'D5 livello 4: mediana di {len(v)} prezzi {prod} per {etichetta}')
        # livello 3: stesso pezzo sulla stessa auto, prezzo noto in un'altra qualità
        # (non per i generici: sono condivisi tra più auto e non hanno un 'pezzo gemello' unico)
        for chiave in ([] if code[0] == 'GENERICO' else chiavi_slot):
            for q_altra, altro in slots.get((chiave, comp), {}).items():
                if q_altra == qual or altro == code or altro not in prezzi_reali or altro == escludi:
                    continue
                r_, origine = rapporto(comp, q_altra, qual, escludi)
                if r_:
                    p0 = prezzi_reali[altro][0]
                    return ('rapporto_qualita', p0 * r_, p0 * r_ * 0.8, p0 * r_ * 1.2, MIN_STAT,
                            f'D5 livello 3: {altro[0]} {altro[1]} ({q_altra}) × rapporto {r_:.2f} ({origine})')
        # livello 5: stessa categoria e qualità, prima nello stesso sottotipo
        prod, st_ = code[0], sott.get(code, '')
        for sotto in ([st_, None] if st_ else [None]):
            etichetta = f'{comp} {sotto}' if sotto else comp
            v = valori(escludi or code, comp, qual, None, sotto)
            if len(v) >= MIN_STAT:
                q1, med, q3 = quartili(v)
                return ('mediana_categoria', med, q1, q3, len(v), f'D5 livello 5: mediana di {len(v)} prezzi {etichetta} {qual}')
        # livello 5b: mediana della stessa categoria nell'altra qualità × rapporto tra qualità
        for q_altra in ('equivalente', 'economico', 'originale'):
            if q_altra == qual:
                continue
            v = valori(escludi or code, comp, q_altra)
            r_, origine = rapporto(comp, q_altra, qual, escludi)
            if len(v) >= MIN_STAT and r_:
                q1, med, q3 = quartili(v)
                return ('mediana_categoria', med * r_, q1 * r_, q3 * r_, len(v),
                        f'D5 livello 5b: mediana di {len(v)} prezzi {comp} {q_altra} × rapporto {r_:.2f} ({origine})')
        return None

    # ricambi generici per i componenti senza codice (qualità equivalente ed economico)
    generici = 0
    for chiave, comps in comp_var.items():
        for comp in sorted(comps):
            for qual in ('equivalente', 'economico'):
                if qual in slots[(chiave, comp)]:
                    continue
                code = ('GENERICO', f'{comp} - {qual}'[:40])
                if code not in code_meta:
                    code_meta[code] = (comp, qual)
                    sql.append(f"INSERT INTO spare_parts (codice, produttore, descrizione, generico) VALUES ({q(code[1])}, 'GENERICO', "
                               f"{q('Segnaposto D5: codice reale non ancora noto')}, TRUE) ON DUPLICATE KEY UPDATE generico = TRUE;")
                slots[(chiave, comp)][qual] = code
                sql.append(
                    "INSERT INTO part_qualities (variante_id, componente_id, qualita, ricambio_id) "
                    f"SELECT {variante_sql(chiave)}, c.id, {q(qual)}, s.id FROM components c, spare_parts s "
                    f"WHERE c.descrizione = {q(comp)} AND s.produttore = 'GENERICO' AND s.codice = {q(code[1])} "
                    "ON DUPLICATE KEY UPDATE ricambio_id = VALUES(ricambio_id);")
                generici += 1

    # stime per tutti i codici ancora senza prezzo
    stime_fatte, senza_prezzo = defaultdict(int), []
    for code, (comp, qual) in sorted(code_meta.items()):
        if code in prezzi_reali:
            continue
        chiavi = [k for (k, c), d in slots.items() if c == comp and d.get(qual) == code]
        st = stima(code, comp, qual, chiavi)
        if not st:
            senza_prezzo.append(f'{code[0]} {code[1]} ({comp}, {qual})')
            continue
        metodo, prezzo, pmin, pmax, n_c, nota = st
        scrivi_prezzo(code[0], code[1], prezzo, 'media', metodo, n_c, pmin, pmax, oggi, nota)
        stime_fatte[metodo] += 1

    # backtest: nascondo ogni prezzo reale e lo ristimo con il metodo
    errori, dettaglio_bt = defaultdict(list), []
    for code, (prezzo, _) in sorted(prezzi_reali.items()):
        if code not in code_meta:
            continue
        comp, qual = code_meta[code]
        chiavi = [k for (k, c), d in slots.items() if c == comp and d.get(qual) == code]
        st = stima(code, comp, qual, chiavi, escludi=code)
        if st:
            errori[st[0]].append(abs(st[1] - prezzo) / prezzo)
            dettaglio_bt.append(f'{code[0]} {code[1]} ({comp}{", " + sott.get(code) if sott.get(code) else ""}): reale {prezzo:.2f}, '
                                f'stima {st[1]:.2f} [{st[0]}], errore {abs(st[1] - prezzo) / prezzo * 100:.0f}%')

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
        origine_t = 'reale' if tipo in ('fattura', 'officina') else 'assunzione'
        sql.append(sql_fonte('fid', nome, aff, tipo, url, d).rstrip('\n'))
        sql.append(f"SET @reg = (SELECT id FROM regions WHERE nome = {q(reg)});")
        sql.append(f"SET @cur = (SELECT id FROM labor_rates WHERE regione_id = @reg AND tipo_officina = {q(off)} AND valido_al IS NULL);")
        sql.append(f"SET @same = (SELECT COUNT(*) FROM labor_rates WHERE id = @cur AND tariffa_oraria_netta = {valore} "
                   f"AND n_campioni = {len(vals)} AND origine = {q(origine_t)});")
        sql.append(f"UPDATE labor_rates SET valido_al = GREATEST(valido_dal, {q(oggi)}) WHERE id = @cur AND @same = 0;")
        sql.append(
            "INSERT INTO labor_rates (regione_id, tipo_officina, tariffa_oraria_netta, origine, n_campioni, data_rilevazione, "
            f"valido_dal, fonte_id) SELECT @reg, {q(off)}, {valore}, {q(origine_t)}, {len(vals)}, {q(d)}, {q(oggi)}, @fid "
            "FROM DUAL WHERE @same = 0;")
        caricati['Tariffe (regione e officina)'] += 1

    # ------------------------------------------------------------ Ricarichi (finché non ci sono tarature da fatture)
    for n, r in righe(wb, 'Ricarichi'):
        off, tp, fatt = testo(r.get('Tipo officina')).lower(), testo(r.get('Tipo prezzo')).lower(), numero(r.get('Ricarico'))
        if off not in OFFICINE or tp not in ('media', 'ufficiale') or fatt is None or not (-0.5 <= fatt <= 2.0):
            scarta('Ricarichi', n, 'tipo officina, tipo prezzo o ricarico non validi (ricarico tra -50% e +200%)')
            continue
        f = controlla_fonte(fonti, 'Ricarichi', n, r)
        if not f:
            continue
        origine_r = 'taratura' if f[2] == 'fattura' else 'assunzione'
        nota_r = testo(r.get('Note'))[:255] or None
        sql.append(f"SET @cur = (SELECT id FROM markups WHERE tipo_officina = {q(off)} AND tipo_fonte = {q(tp)} AND valido_al IS NULL);")
        sql.append(f"SET @same = (SELECT COUNT(*) FROM markups WHERE id = @cur AND fattore = {fatt} AND origine = {q(origine_r)});")
        sql.append(f"UPDATE markups SET valido_al = GREATEST(valido_dal, {q(oggi)}) WHERE id = @cur AND @same = 0;")
        sql.append("INSERT INTO markups (tipo_officina, tipo_fonte, fattore, origine, n_campioni, valido_dal, note) "
                   f"SELECT {q(off)}, {q(tp)}, {fatt}, {q(origine_r)}, 0, {q(oggi)}, {q(nota_r)} FROM DUAL WHERE @same = 0;")
        caricati['Ricarichi'] += 1

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
               "SELECT CONCAT('  di cui ', metodo), COUNT(*) FROM part_prices WHERE valido_al IS NULL GROUP BY metodo UNION ALL "
               "SELECT 'reference_prices', COUNT(*) FROM reference_prices UNION ALL "
               "SELECT 'price_samples', COUNT(*) FROM price_samples UNION ALL "
               "SELECT 'sources', COUNT(*) FROM sources UNION ALL "
               "SELECT 'labor_hours', COUNT(*) FROM labor_hours UNION ALL "
               "SELECT 'labor_rates (correnti)', COUNT(*) FROM labor_rates WHERE valido_al IS NULL UNION ALL "
               "SELECT 'markups (correnti)', COUNT(*) FROM markups WHERE valido_al IS NULL UNION ALL "
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
    lines += ["", "Stime D5 (da avvalorare):"]
    lines += [f"  {m}: {n} codici" for m, n in sorted(stime_fatte.items())] or ["  nessuna"]
    lines += [f"  ricambi generici creati (componenti senza codice): {generici}"]
    lines += ["", "Ancora senza prezzo:"] + ([f"  {x}" for x in senza_prezzo] or ["  nessuno"])
    lines += ["", "Backtest (prezzo reale nascosto e ristimato): errore medio assoluto per metodo"]
    lines += [f"  {m}: {statistics.median(e) * 100:.0f}% (mediana su {len(e)} prezzi reali)" for m, e in sorted(errori.items())] or ["  non calcolabile"]
    tutti = [x for e in errori.values() for x in e]
    if tutti:
        lines += [f"  tutti i metodi: {statistics.median(tutti) * 100:.0f}% (mediana su {len(tutti)} prezzi reali)"]
    lines += [f"    {x}" for x in dettaglio_bt]
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
