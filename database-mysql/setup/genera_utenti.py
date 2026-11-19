"""EQUO - genera lo script dei tre utenti MySQL e i file di credenziali locali (decisione D8).

Uso, dalla cartella del progetto:
    python database-mysql/setup/genera_utenti.py
    powershell -ExecutionPolicy Bypass -File database-mysql/setup/crea_utenti_root.ps1   (chiede la password di root)

Scrive:
  setup/01_utenti_separati.sql        modello con password segnaposto (su git)
  setup/01_utenti_separati.local.sql  stesso script con le password vere (escluso da git)
  equo_api/equo_import/equo_schema.local.cnf   credenziali per i comandi mysql (escluse da git)
  ../backend-laravel/.env             credenziali dei tre collegamenti Laravel (escluso da git)
  ../backend-laravel/.env.example     stessi nomi, password vuote (su git)

Rilanciarlo quando si aggiunge una tabella (va messa nelle liste qui sotto): le password esistenti vengono riusate.
"""
import os
import re
import secrets
import string

DB = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))      # database-mysql
BE = os.path.join(os.path.dirname(DB), 'backend-laravel')

TABELLE = ('brands,car_models,components,intervention_components,interventions,labor_hours,labor_rates,'
           'market_benchmarks,markups,part_prices,part_qualities,price_samples,quote_requests,reference_prices,regions,'
           'settings,sources,spare_parts,variants').split(',')
CATALOGO = [t for t in TABELLE if t != 'quote_requests']
# tabelle di Laravel usate dall'area riservata (account, sessioni, cache); 'migrations' resta solo a equo_schema.
# password_reset_tokens, jobs, job_batches e failed_jobs sono state tolte il 16/11/2026: niente recupero via email
# e niente lavori in background.
LARAVEL = ['users', 'sessions', 'cache', 'cache_locks']
DATABASE = ['equo', 'equo_test']


def password():
    alfabeto = string.ascii_letters + string.digits + '-_.'
    return ''.join(secrets.choice(alfabeto) for _ in range(32))


def utente(nome, pw):
    return (f"CREATE USER IF NOT EXISTS '{nome}'@'localhost' IDENTIFIED BY '{pw}';\n"
            f"ALTER USER '{nome}'@'localhost' IDENTIFIED BY '{pw}';\n"
            f"REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{nome}'@'localhost';")


def script(pw_api, pw_import, pw_schema):
    s = ["-- =====================================================================",
         "-- EQUO — 01 · Tre utenti MySQL separati (decisione D8, task L1; tabelle Laravel e equo_test dal task L2)",
         "-- Da eseguire come root, dopo 00_crea_database.sql e 02_database_test.sql. Rieseguibile: riallinea password e permessi.",
         "-- Generato da genera_utenti.py: modificare quello, non questo file.",
         "--",
         "-- Questo file è il modello versionato: le password sono segnaposto.",
         "-- La copia con le password vere è 01_utenti_separati.local.sql (non va su git).",
         "--",
         "--   equo_api     usato dall'API per il sito React: legge il catalogo, aggiunge richieste anonime.",
         "--                Non può modificare né cancellare niente: anche con il codice compromesso, MySQL rifiuta.",
         "--   equo_import  usato dall'import e dall'area riservata: legge e scrive le tabelle dei dati.",
         "--   equo_schema  solo per creare e modificare le tabelle (migration), lanciato a mano.",
         "--",
         "-- I permessi sono dati tabella per tabella: una tabella nuova non è accessibile finché non la si aggiunge qui.",
         "-- L'utente equo_app di 00_crea_database.sql dal task L9 non è più usato da nessuno strumento: si può togliere (DROP USER, come root).",
         "-- Gli stessi permessi valgono per equo (dati veri) e equo_test (database di prova dei test automatici);",
         "-- le tabelle devono esistere: equo_test si costruisce prima con le migration.",
         "-- =====================================================================",
         ""]
    s.append("-- equo_api: sola lettura del catalogo + inserimento in quote_requests; nessun accesso alle tabelle dell'area riservata")
    s.append(utente('equo_api', pw_api))
    for db in DATABASE:
        s += [f"GRANT SELECT ON {db}.{t} TO 'equo_api'@'localhost';" for t in CATALOGO]
        s.append(f"GRANT INSERT ON {db}.quote_requests TO 'equo_api'@'localhost';")
    s.append("")
    s.append("-- equo_import: dati (lettura e scrittura), procedure, tabelle di Laravel per l'area riservata;")
    s.append("-- il registro delle azioni si può solo leggere e aggiungere, mai modificare o cancellare")
    s.append(utente('equo_import', pw_import))
    for db in DATABASE:
        s += [f"GRANT SELECT, INSERT, UPDATE, DELETE ON {db}.{t} TO 'equo_import'@'localhost';" for t in TABELLE + LARAVEL]
        s.append(f"GRANT SELECT, INSERT ON {db}.admin_audit_log TO 'equo_import'@'localhost';")
        s.append(f"GRANT EXECUTE, CREATE TEMPORARY TABLES ON {db}.* TO 'equo_import'@'localhost';")
    s.append("")
    s.append("-- equo_schema: struttura del database (migration); mai usato dall'app in esercizio")
    s.append(utente('equo_schema', pw_schema))
    for db in DATABASE:
        s.append(f"GRANT ALL PRIVILEGES ON {db}.* TO 'equo_schema'@'localhost';")
    s.append("")
    s.append("-- controllo finale")
    s += [f"SHOW GRANTS FOR '{u}'@'localhost';" for u in ('equo_api', 'equo_import', 'equo_schema')]
    return '\n'.join(s) + '\n'


def imposta(path, valori):
    s = open(path, encoding='utf-8').read()
    for k, v in valori.items():
        if re.search(rf'^#?\s*{k}=.*$', s, flags=re.M):
            s = re.sub(rf'^#?\s*{k}=.*$', f'{k}={v}', s, count=1, flags=re.M)
        else:
            s = s.rstrip('\n') + f'\n{k}={v}\n'
    open(path, 'w', encoding='utf-8').write(s)


locale = os.path.join(DB, 'setup', '01_utenti_separati.local.sql')
if os.path.exists(locale):
    # riusa le password esistenti: lo script resta rieseguibile senza rompere i collegamenti
    pws = dict(re.findall(r"ALTER USER '(\w+)'@'localhost' IDENTIFIED BY '([^']+)'", open(locale, encoding='utf-8').read()))
else:
    pws = {u: password() for u in ('equo_api', 'equo_import', 'equo_schema')}

open(os.path.join(DB, 'setup', '01_utenti_separati.sql'), 'w', encoding='utf-8').write(
    script('CAMBIA_PASSWORD_API', 'CAMBIA_PASSWORD_IMPORT', 'CAMBIA_PASSWORD_SCHEMA'))
open(locale, 'w', encoding='utf-8').write(script(pws['equo_api'], pws['equo_import'], pws['equo_schema']))

for u, pw in pws.items():
    open(os.path.join(DB, f'{u}.local.cnf'), 'w', encoding='utf-8').write(
        f"[client]\nuser={u}\npassword={pw}\nhost=127.0.0.1\nport=3306\ndefault-character-set=utf8mb4\n")

# Laravel: collegamento principale con equo_api; 'import' e 'schema' con gli altri due utenti
comuni = {'APP_NAME': 'EQUO', 'APP_LOCALE': 'it', 'APP_FALLBACK_LOCALE': 'it', 'APP_FAKER_LOCALE': 'it_IT',
          'DB_CONNECTION': 'mysql', 'DB_HOST': '127.0.0.1', 'DB_PORT': '3306', 'DB_DATABASE': 'equo',
          'DB_USERNAME': 'equo_api', 'DB_COLLATION': 'utf8mb4_0900_ai_ci',
          'DB_IMPORT_USERNAME': 'equo_import', 'DB_SCHEMA_USERNAME': 'equo_schema', 'DB_SCHEMA_DATABASE': 'equo',
          'QUEUE_CONNECTION': 'sync',
          # niente lavori in background: i lavori falliti non vanno in una tabella (failed_jobs non esiste più)
          'QUEUE_FAILED_DRIVER': 'null',
          # area riservata (task L7): sessioni e cache su database con l'utente equo_import (equo_api non le vede);
          # sessione di 30 minuti, chiusa con il browser, cifrata, cookie non leggibile dagli script e mai mandato da altri siti
          'SESSION_DRIVER': 'database', 'SESSION_CONNECTION': 'import', 'SESSION_LIFETIME': '30',
          'SESSION_EXPIRE_ON_CLOSE': 'true', 'SESSION_ENCRYPT': 'true', 'SESSION_HTTP_ONLY': 'true',
          'SESSION_SAME_SITE': 'strict', 'SESSION_COOKIE': 'equo_sessione',
          'CACHE_STORE': 'database', 'DB_CACHE_CONNECTION': 'import', 'DB_CACHE_LOCK_CONNECTION': 'import',
          'DB_CACHE_LOCK_TABLE': 'cache_locks'}
if os.path.exists(os.path.join(BE, '.env')):
    # sul computer di sviluppo si usa http://localhost: il cookie non può essere "solo HTTPS"
    imposta(os.path.join(BE, '.env'), {**comuni, 'DB_PASSWORD': pws['equo_api'], 'DB_IMPORT_PASSWORD': pws['equo_import'],
                                       'DB_SCHEMA_PASSWORD': pws['equo_schema'], 'SESSION_SECURE_COOKIE': 'false'})
imposta(os.path.join(BE, '.env.example'), {**comuni, 'DB_PASSWORD': '', 'DB_IMPORT_PASSWORD': '', 'DB_SCHEMA_PASSWORD': '',
                                           'SESSION_SECURE_COOKIE': 'true'})
print('ok:', ', '.join(f'{u} ({len(p)} caratteri)' for u, p in pws.items()))
