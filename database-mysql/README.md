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
| `schema/01_schema.sql` | crea le 18 tabelle (rieseguibile: cancella i dati) | sì |
| `seed/02_dati_fissi.sql` | regioni, interventi, componenti, parametri | sì |
| `import/` | script di caricamento della raccolta prezzi (M4) | sì (i dati no) |
| `queries/` | calcolo stima, controlli di copertura, taratura (M6-M8) | sì |
| `backup/` | dump locali del database | **no** (solo la cartella) |
| `SCHEMA.md` | tabelle, collegamenti, corrispondenza con i nomi vecchi | sì |
| `.env.example` | modello delle variabili di connessione, password vuota | sì |
| `.env`, `equo_app.local.cnf` | credenziali di `equo_app` | **no** |

Il diagramma delle tabelle è in [`../docs/architettura/schema-database-equo.png`](../docs/architettura/schema-database-equo.png).

Le password stanno solo nei file `.local.*` e `.env`, esclusi da git. Su una macchina nuova: copia `.env.example` in `.env`, copia `setup/00_crea_database.sql` in `setup/00_crea_database.local.sql` e scrivi in entrambi la stessa password; poi crea `equo_app.local.cnf` con le stesse credenziali (sezione `[client]`).

Quando parte la fase Laravel, lo schema passa alle migration di `backend-laravel/` e da lì si modifica solo lì: `schema/01_schema.sql` resta come riferimento.

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

## Le tabelle

Nomi delle tabelle in inglese, nomi delle colonne in italiano.

| Gruppo | Tabelle |
|---|---|
| Parametri e fonti | `settings`, `regions`, `sources` |
| Veicoli | `brands`, `car_models`, `variants` |
| Interventi | `interventions`, `components`, `intervention_components` |
| Ricambi e prezzi | `spare_parts`, `part_qualities`, `part_prices`, `price_samples` |
| Manodopera e ricarico | `labor_hours`, `labor_rates`, `markups`, `calibrations` |
| Utenti (anonimi) | `quote_requests` |

Note su alcune tabelle:

- **`intervention_components`**: ogni riga dice *per questa variante, in questo intervento, serve questo componente in questa quantità*. Servono sia intervento sia componente perché un intervento ha più pezzi e lo stesso pezzo compare in più interventi (il filtro aria sta nel tagliando e nel "Filtro aria" singolo).
- **`price_samples`**: ogni prezzo di mercato è la mediana di 3 rilevazioni, e ognuna ha la sua fonte (URL, data, screenshot).
- **`quote_requests`**: ogni verifica fatta da un utente, senza dati personali. Se l'intervento non è in elenco si salva il testo in `altro_intervento`: ci dice quali interventi aggiungere.
- **`car_models.info_completed`**: TRUE quando i dati del modello sono completi; solo allora il modello compare nel form.
- **`variants.anno_produzione_inizio / fine`**: anni in cui quella motorizzazione è stata prodotta. Il form propone solo le varianti compatibili con l'anno di immatricolazione.

## Regole incorporate nello schema

- **Importi netti di IVA.** L'IVA (`settings.iva_pct`) si applica una volta, alla fine.
- **Un solo valore corrente.** Prezzi, tariffe e ricarichi hanno `valido_dal` / `valido_al`; la riga con `valido_al` vuoto è quella in uso, e il database ne impedisce due per la stessa chiave.
- **Ricarico per tipo officina + tipo di prezzo** (`ufficiale` / `media`), non per regione.
- **Olio in litri, per variante**: la quantità sta in `intervention_components`.
- **Codice ricambio unico per produttore** (`produttore` + `codice`), perché produttori diversi possono usare lo stesso codice.
- **Cinghia solo se serve**: l'intervento "Cinghia distribuzione" non si propone per le varianti con `distribuzione = catena`.
- **Pezzi singoli nascosti** (`interventions.visibile_nel_form = 0`) finché il PM non approva la lista.
- **Richiesta valida**: in `quote_requests` serve un intervento dal menu oppure il testo di `altro_intervento`.
- **Nessun numero inventato**: prezzi, tariffe, ore e ricarichi sono vuoti finché non arrivano dalla raccolta.
