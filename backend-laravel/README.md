# Backend Laravel

Il backend di EQUO: legge il database `equo` (vedi [`../database-mysql`](../database-mysql/README.md)), calcola la stima e il verdetto per il sito React, salva le richieste anonime in `quote_requests` e offre un'area riservata all'amministratore.

**Stato:** L1 fatto (16/11/2026): Laravel 13.42 su PHP 8.5, collegato al database come `equo_api`. Struttura e sicurezza approvate il 16/11/2026: [`../docs/architettura/struttura-backend-laravel.html`](../docs/architettura/struttura-backend-laravel.html).

## Requisiti e avvio

- **PHP 8.5** in `C:\php85` (separato dal PHP 8.2 di XAMPP, che resta per il resto), con le estensioni `intl`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `sodium`.
- **Composer** lanciato con quel PHP: `C:\php85\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install`.
- Copia `.env.example` in `.env`, poi `C:\php85\php.exe artisan key:generate` e la password di `equo_api` in `DB_PASSWORD` (da `../database-mysql/setup/01_utenti_separati.local.sql`).
- I tre utenti MySQL si creano una volta con `../database-mysql/setup/crea_utenti_root.ps1` (chiede la password di root).
- Prova: `C:\php85\php.exe artisan test`.

## Migration (L2, 16/11/2026)

- **Le 20 tabelle di EQUO** hanno una migration ciascuna (`database/migrations/2026_11_16_1000NN_create_*`), con lo stesso `CREATE TABLE` provato in MySQL: vincoli CHECK, colonne calcolate, commenti e nomi delle chiavi identici.
- **Tabelle in più:** `users` (account amministratore), `sessions`, `cache`, `cache_locks` e `admin_audit_log`, il registro delle azioni dell'area riservata.
- **Si lanciano solo con l'utente `equo_schema`:** `C:\php85\php.exe artisan migrate --database=schema`. Gli altri due utenti non possono cambiare la struttura.
- **Prova:** `equo_test` costruito solo dalle migration ha le 20 tabelle identiche a `equo`. In `equo` le 20 tabelle esistenti sono registrate come già create (gruppo 1) e le 10 nuove sono state aggiunte (gruppo 2) senza toccare i dati.
- **Permessi sulle tabelle nuove:** `equo_api` non vede né `users` né il registro; `equo_import` usa le tabelle di Laravel e sul registro può solo leggere e aggiungere (modifica e cancellazione rifiutate da MySQL).

- **Fatture tolte:** la migration `2026_11_16_100200_drop_calibrations_table` cancella la tabella delle fatture reali (vuota, le fatture non arriveranno) e la sua impostazione `min_campioni_taratura`; `down()` le rimette. Dopo la modifica `equo` e `equo_test` hanno 29 tabelle, identiche una per una.

- **Tabelle Laravel tolte:** la migration `2026_11_16_100300_drop_unused_laravel_tables` toglie `password_reset_tokens` (niente recupero password via email: l'accesso si recupera da comando sul server) e `jobs`, `job_batches`, `failed_jobs` (niente lavori in background; `QUEUE_FAILED_DRIVER=null`). `down()` le rimette. Il database ha 25 tabelle: 19 di dati e 6 per l'area riservata.

Sessioni e cache restano per ora su file (`SESSION_DRIVER=file`, `CACHE_STORE=file`); con l'area riservata (L7) passano su database, per vedere e chiudere gli accessi attivi e tenere i limiti di traffico anche dopo un riavvio.

## Prova del 16/11/2026 (L1)

Collegato come `equo_api`, il database **rifiuta**: modifica e cancellazione di un prezzo, inserimento nelle impostazioni, cambio di una soglia del verdetto, svuotamento e cancellazione di tabelle, modifica della struttura, esecuzione delle procedure, lettura di altri database e lettura delle richieste degli utenti. **Permette** solo la lettura del catalogo e l'inserimento di una richiesta anonima. Lo stesso rifiuto arriva quando la modifica parte dal codice Laravel.

## Perché serve, se c'è già React

React gira nel browser dell'utente: tutto quello che contiene è leggibile da chiunque. Se si collegasse direttamente al database, le credenziali di MySQL sarebbero nella pagina. Per questo React chiede dati e calcolo a Laravel, che gira sul server e tiene nascoste le credenziali.

```
Utente → React (browser) → Laravel (server) → MySQL
```

## Due ingressi

| Ingresso | Chi | Cosa può fare |
|---|---|---|
| API per il sito React (`/api/…`) | il sito React, senza login | solo leggere il catalogo e aggiungere la richiesta anonima |
| Area riservata (`/gestione`) | solo l'amministratore, con password e verifica in due passaggi | vedere lo stato dei dati, lanciare l'import (prova → conferma), impostazioni, backup, registro azioni |

Non esiste nessuna rotta per modificare a mano prezzi, codici, ore o tariffe: i dati entrano solo dall'import, con fonte, data e link.

## Rotte dell'API

| Metodo | Rotta | Cosa fa | Limite |
|---|---|---|---|
| GET | `/api/opzioni` | regioni, marche, modelli, interventi, qualità, tipi officina | 120/min |
| GET | `/api/varianti?modello_id=` | varianti di un modello | 120/min |
| POST | `/api/verifica` | stima + verdetto, salva la richiesta anonima | 20/min, 200/giorno |
| GET | `/api/salute` | solo "ok" o "errore", senza dettagli interni | 120/min |

## Sicurezza, in breve

- Middleware su tre livelli: tutte le richieste (HTTPS, intestazioni di sicurezza, CORS solo dal sito React, limite dimensione), API (solo JSON, solo GET e una POST, limiti di traffico, validazione), area riservata (cookie cifrati, CSRF, limite tentativi, due fattori, ruolo, scadenza sessione, registro azioni).
- Utenti MySQL separati: `equo_api` (solo lettura + inserimento in `quote_requests`), `equo_import` (import e area riservata), `equo_schema` (solo migration). L'API non può modificare i dati nemmeno se il codice venisse compromesso.
- Credenziali solo in `.env` (escluso da git); `APP_DEBUG=false` in produzione; nessun dato personale salvato.

## Piano

I passi L1-L9 sono nel documento di struttura (sezione 10).
