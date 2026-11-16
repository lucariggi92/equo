# Database MySQL

Database del pilota EQUO V2: stima scomponibile e verificabile del costo di un intervento (ricambi + ricarico + manodopera + IVA), con una fonte per ogni prezzo.

## Requisiti

- MySQL Server **8.4** (servizio `MySQL84`, porta 3306). Non la MariaDB di XAMPP.
- MySQL Workbench 8.0 per consultare i dati (facoltativo).

## Struttura

| Cartella / file | Cosa contiene | Su git |
|---|---|---|
| `setup/00_crea_database.sql` | modello: database `equo` + utente `equo_app` | sì (password segnaposto) |
| `setup/00_crea_database.local.sql` | come sopra, con la password vera | **no** |
| `setup/crea_database_root.ps1` | esegue lo script 00 come root (chiede la password) | sì |
| `setup/01_utenti_separati.sql` | modello: utenti `equo_api` (sola lettura + richieste anonime), `equo_import` (scrittura dati), `equo_schema` (struttura) | sì (password segnaposto) |
| `setup/01_utenti_separati.local.sql` | come sopra, con le password vere (generate casuali) | **no** |
| `setup/crea_utenti_root.ps1` | esegue lo script 01 come root (chiede la password); rieseguibile | sì |
| `setup/genera_utenti.py` | rigenera lo script 01, i file `.local.cnf` e le credenziali in `backend-laravel/.env`; da rilanciare quando si aggiunge una tabella | sì |
| `setup/02_database_test.sql`, `setup/crea_database_test_root.ps1` | database di prova `equo_test` per le migration e i test automatici (come root) | sì |
| `schema/01_schema.sql` | riferimento storico dello schema della fase 1 (20 tabelle); non va più rieseguito: lo schema ora sta nelle migration | sì |
| `seed/02_dati_fissi.sql` | regioni, interventi, componenti, parametri | sì |
| `import/` | `importa_dati.py`: controlla e carica `dati-disponibili.xlsx` (M4) | sì (i dati no) |
| `queries/` | procedure: stima e verdetto (M6), controlli di copertura (M7), taratura (M8) | sì |
| `maintenance/` | `backup.ps1` e `ripristina.ps1` (M9) | sì |
| `backup/` | dump locali del database con il loro inventario | **no** (solo la cartella) |
| `SCHEMA.md` | tabelle, collegamenti, corrispondenza con i nomi vecchi | sì |
| `.env.example` | modello delle variabili di connessione, password vuota | sì |
| `.env`, `equo_app.local.cnf` | credenziali di `equo_app` | **no** |
| `equo_api.local.cnf`, `equo_import.local.cnf`, `equo_schema.local.cnf` | credenziali dei tre utenti separati, per i comandi `mysql` | **no** |

Il diagramma delle tabelle è in [`../docs/architettura/schema-database-equo.png`](../docs/architettura/schema-database-equo.png).

Le password stanno solo nei file `.local.*` e `.env`, esclusi da git. Su una macchina nuova: copia `.env.example` in `.env`, copia `setup/00_crea_database.sql` in `setup/00_crea_database.local.sql` e scrivi in entrambi la stessa password; poi crea `equo_app.local.cnf` con le stesse credenziali (sezione `[client]`).

**Dal 16/11/2026 (task L2) lo schema si modifica solo con le migration di `backend-laravel/`.** La tabella delle fatture reali (`calibrations`) è stata tolta lo stesso giorno con una migration: le fatture non arriveranno (decisione D7) e la tabella era vuota. Oggi le tabelle dei dati sono 19. Sono state tolte anche quattro tabelle standard di Laravel che EQUO non usa (`password_reset_tokens`, `jobs`, `job_batches`, `failed_jobs`): in tutto il database ha 25 tabelle. Le 20 tabelle hanno ognuna la sua migration, con lo stesso `CREATE TABLE` di `schema/01_schema.sql`; la prova ha costruito `equo_test` solo dalle migration e le 20 tabelle sono risultate identiche a quelle di `equo`. `schema/01_schema.sql` resta come riferimento storico: non va più rieseguito sul database vero.

## Installazione da zero

Dalla cartella `database-mysql`:

1. Come root, una volta sola:
   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\setup\crea_database_root.ps1
   ```
2. Schema e dati fissi, con l'utente `equo_app`:
   ```powershell
   & 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe' --defaults-extra-file=equo_app.local.cnf -e "source schema/01_schema.sql; source seed/02_dati_fissi.sql"
   ```

## Backup e ripristino

Dalla cartella del progetto (usano `equo_app.local.cnf`, nessuna password da digitare):

```powershell
powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\backup.ps1
powershell -ExecutionPolicy Bypass -File database-mysql\maintenance\ripristina.ps1
```

- **`backup.ps1`** salva in `backup/` il dump completo (tabelle, dati, procedure) `equo-AAAAMMGG-HHMM.sql` e accanto
  `equo-AAAAMMGG-HHMM.inventario.txt`, con le righe di ogni tabella e i nomi delle procedure.
- **`ripristina.ps1`** prende l'ultimo backup (oppure `-File <dump.sql>`), **svuota** il database di destinazione
  (`-Database`, di default `equo`), carica il dump e confronta il risultato con l'inventario: esce con 0 se è identico,
  con 1 e l'elenco delle differenze se non lo è. Chiede di scrivere il nome del database per conferma (`-Forza` la salta).
- Il dump non contiene `CREATE DATABASE` né `USE`: si può caricare in un altro database, purché esista e `equo_app` ci possa scrivere
  (lo crea root, come in `setup/`).
- Quando farlo: prima di rieseguire `schema/01_schema.sql` (cancella i dati), prima di un import importante, e prima di ogni modifica
  allo schema. I dump restano solo su questo computer: per una copia esterna vanno copiati a mano su un disco o un cloud personale.
- Prova del 2026-11-16 (M9): backup di 20 tabelle e 4 procedure, database svuotato e ripristinato, inventario identico;
  stima di controllo invariata (tagliando Panda, generica, Lombardia: 242,84 €). Il confronto segnala correttamente
  una differenza inserita apposta.

## Le tabelle

Nomi delle tabelle in inglese, nomi delle colonne in italiano.

| Gruppo | Tabelle |
|---|---|
| Parametri e fonti | `settings`, `regions`, `sources` |
| Veicoli | `brands`, `car_models`, `variants` |
| Interventi | `interventions`, `components`, `intervention_components` |
| Ricambi e prezzi | `spare_parts`, `part_qualities`, `part_prices`, `price_samples`, `reference_prices` |
| Manodopera e ricarico | `labor_hours`, `labor_rates`, `markups`, `market_benchmarks` |
| Area riservata (Laravel) | `users`, `sessions`, `cache`, `cache_locks`, `admin_audit_log`, `migrations` |
| Utenti (anonimi) | `quote_requests` |

Note su alcune tabelle:

- **`intervention_components`**: ogni riga dice *per questa variante, in questo intervento, serve questo componente in questa quantità*. Servono sia intervento sia componente perché un intervento ha più pezzi e lo stesso pezzo compare in più interventi (il filtro aria sta nel tagliando e nel "Filtro aria" singolo).
- **`price_samples`**: ogni prezzo di mercato è la mediana di almeno 2 rilevazioni da negozi indipendenti, e ognuna ha la sua fonte (URL, data, screenshot).
- **`quote_requests`**: ogni verifica fatta da un utente, senza dati personali. Se l'intervento non è in elenco si salva il testo in `altro_intervento`: ci dice quali interventi aggiungere.
- **`car_models.info_completed`**: TRUE quando i dati del modello sono completi; solo allora il modello compare nel form.
- **`market_benchmarks`**: tariffe orarie e prezzi al cliente pubblicati da indagini e riviste. Servono alla taratura (M8) al posto delle fatture: confermano o mettono in dubbio i valori EQUO, non li cambiano da soli.
- **`labor_hours.origine`**: `tempario` se le ore vengono da un tempario pubblicato per quella versione, `assunzione` se sono ancora i valori iniziali D6, `reale` se vengono da fatture od officine.
- **`variants.anno_produzione_inizio / fine`**: anni in cui quella motorizzazione è stata prodotta. Il form propone solo le varianti compatibili con l'anno di immatricolazione.

## Regole incorporate nello schema

- **Importi netti di IVA.** L'IVA (`settings.iva_pct`) si applica una volta, alla fine.
- **Un solo valore corrente.** Prezzi, tariffe e ricarichi hanno `valido_dal` / `valido_al`; la riga con `valido_al` vuoto è quella in uso, e il database ne impedisce due per la stessa chiave.
- **Ricarico per tipo officina + tipo di prezzo** (`ufficiale` / `media`), non per regione.
- **Olio in litri, per variante**: la quantità sta in `intervention_components`.
- **Codice ricambio unico per produttore** (`produttore` + `codice`), perché produttori diversi possono usare lo stesso codice.
- **Cinghia solo se serve**: l'intervento "Cinghia distribuzione" non si propone per le varianti con `distribuzione = catena`.
- **Frizione solo se serve**: l'intervento "Frizione" non si propone per le varianti con `cambio = automatico` (convertitore di coppia, e-CVT delle ibride; per ora anche il doppia frizione). Manuale e robotizzato hanno la frizione. Il controllo di copertura non conta questi interventi né come calcolabili né come mancanti; se il dato tecnico manca, l'intervento resta e conta come mancante.
- **Interventi nel form**: pacchetti e pezzi singoli sono tutti visibili (`interventions.visibile_nel_form = 1`); il form li mostra in un unico elenco con barra di ricerca. Con `0` un intervento si nasconde senza cancellarlo.
- **Ogni fonte ha un grado** (`sources.affidabilita`: basso / medio / elevato, dal file `import/elenco-fonti.xlsx`). L'import usa le fonti `elevato` anche con un solo valore, le `medio` solo come mediana di almeno 2 negozi indipendenti, le `basso` mai.
- **Richiesta valida**: in `quote_requests` serve un intervento dal menu oppure il testo di `altro_intervento`.
- **Nessun numero senza metodo e senza etichetta (D5)**: ogni prezzo ha un `metodo` (rilevato, indicativo, rapporto_qualita, mediana_marca, mediana_categoria). Tutto ciò che non è `rilevato` è `da_avvalorare` e porta mediana, intervallo e numero di campioni. La base statistica sta in `reference_prices`; i componenti senza codice usano un ricambio `generico` di categoria.
