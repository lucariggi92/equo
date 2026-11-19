# Backend Laravel

Il backend di EQUO: legge il database `equo` (vedi [`../database-mysql`](../database-mysql/README.md)), calcola la stima e il verdetto per il sito React, salva le richieste anonime in `quote_requests` e offre un'area riservata all'amministratore.

**Stato:** L1 fatto (16/11/2026): Laravel 13.42 su PHP 8.5, collegato al database come `equo_api`. Struttura e sicurezza approvate il 16/11/2026: [`../docs/architettura/struttura-backend-laravel.html`](../docs/architettura/struttura-backend-laravel.html).

## Requisiti e avvio

- **PHP 8.5** in `C:\php85` (separato dal PHP 8.2 di XAMPP, che resta per il resto), con le estensioni `intl`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, `sodium`.
- **Composer** lanciato con quel PHP: `C:\php85\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install`.
- Copia `.env.example` in `.env`, poi `C:\php85\php.exe artisan key:generate` e la password di `equo_api` in `DB_PASSWORD` (da `../database-mysql/setup/01_utenti_separati.local.sql`).
  `.env.example` ha i valori di produzione (`APP_ENV=production`, `APP_DEBUG=false`): sul computer di sviluppo mettere `APP_ENV=local` e `APP_DEBUG=true`.
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

## Modelli (L3, 16/11/2026)

- **20 modelli in `app/Models`**, uno per tabella (più `User`), con le relazioni principali: marca → modelli → versioni → composizione degli interventi, ricambi per qualità, prezzo corrente e campioni dei negozi, ore, tariffe e ricarichi correnti (`->correnti()`).
- **Catalogo in sola lettura** (`Concerns\SolaLettura`): salvataggio, creazione, modifica e cancellazione rifiutati, anche nelle varianti "quiet" e nelle scritture di massa (`update`, `delete`, `insert`, `upsert`, `increment`, `truncate`). I dati entrano solo dall'import.
- **Solo aggiunta** (`Concerns\SoloAggiunta`) per `QuoteRequest` (richieste degli utenti) e `AdminAuditLog` (registro): si crea una riga, poi non si tocca più.
- **`User` e `AdminAuditLog`** usano il collegamento `import` (`equo_import`): l'API (`equo_api`) non vede account né registro.

## Calcolo della stima (L4, 16/11/2026)

- **`App\Services\Stima\CalcoloStima`**: stessa logica della procedura MySQL `calcola_stima` (fase 1): ricambi con prezzo e ricarico riga per riga, manodopera con le ore minime, IVA una volta alla fine, fascia da–a, quota di prezzi verificati / da un solo negozio / stimati, completezza (manca un prezzo, la tariffa, le ore o l'olio nel tagliando) e verdetto (equo fino a +10%, sopra la media fino a +25%, oltre anomalo; sotto −25% equo con avviso).
- **Aritmetica decimale esatta** (`Decimale`, con l'estensione PHP `bcmath`, dichiarata in `composer.json`): gli importi viaggiano come stringhe e si arrotondano come in MySQL. Nessun errore da numeri con la virgola mobile.
- **Verifica:** `C:\php85\php.exe artisan equo:confronta-stime` confronta PHP e MySQL campo per campo e riga per riga su ogni versione × intervento × qualità × tipo di officina × regione (una per area; `--tutte-le-regioni` per tutte e 20), più 4 preventivi per stima (−30%, +10%, +25%, +40%: sotto, sulle due soglie e oltre). Il 16/11/2026: **14.202 casi, tutti identici**, in circa 7 minuti.
- I test automatici (`tests/Feature/CalcoloStimaTest.php`) fissano 10 casi con i valori restituiti dalla procedura MySQL.

## Controlli di sicurezza (L6, 16/11/2026)

Registrati in `bootstrap/app.php`, valgono per ogni richiesta (codice in `app/Http/Middleware`):

| Controllo | Cosa fa |
|---|---|
| `IdRichiesta` | un codice unico per richiesta, generato dal server, nella risposta (`X-Request-Id`) e in ogni riga di log |
| `IntestazioniSicurezza` | `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`; sull'API una CSP che non permette di caricare né eseguire nulla; toglie `X-Powered-By` |
| `ForzaHttps` | con `EQUO_FORZA_HTTPS=true` (produzione): le letture in chiaro passano su HTTPS, il resto viene rifiutato; HSTS di un anno sulle risposte cifrate |
| CORS (`config/cors.php`) | solo il sito React (`FRONTEND_URL`), solo GET e POST, nessun cookie |
| `TrustProxies` | ci si fida solo del proxy in `TRUSTED_PROXIES` (di default di nessuno): nessuno può fingere un altro indirizzo per aggirare i limiti |
| `ControlloRichiesteApi` | sull'API: solo GET/HEAD, POST solo su `/api/verifica`; modifiche e cancellazioni rifiutate anche se travestite (`X-HTTP-Method-Override`, `_method`); oltre 16 KB rifiutata; le POST devono essere JSON |

Tolte la pagina di benvenuto di Laravel e la rotta `/up`: nessuna pagina pubblica rivela il software usato. Con `APP_DEBUG=false` gli errori rispondono solo `Server Error`.

## Area riservata `/gestione` (L7, 16/11/2026)

Pannello Filament 5 (`app/Providers/Filament/GestionePanelProvider.php`), con l'utente MySQL `equo_import`.

- **Account:** si crea solo sul server con `C:\php85\php.exe artisan equo:crea-amministratore` (password di almeno 14 caratteri con maiuscole, minuscole, numeri e simboli). Non esistono pagina di registrazione né recupero via email: se si perde l'accesso, `artisan equo:recupera-accesso <email>` cambia la password, chiude le sessioni aperte e, con `--azzera-due-fattori`, toglie il codice del telefono.
- **Chi entra:** solo gli account con ruolo `amministratore`. Massimo 5 tentativi al minuto. Facoltativo: solo da indirizzi ammessi (`EQUO_GESTIONE_IP`).
- **Verifica in due passaggi** (codice dall'app del telefono, con codici di recupero): interruttore `EQUO_GESTIONE_DUE_FATTORI`. **Spenta per decisione del PM del 16/11/2026** sul computer di sviluppo; accesa nel modello `.env.example` e nei test. **Va riaccesa prima di mettere l'area riservata online.**
- **Registro:** ogni accesso, tentativo fallito, blocco per troppi tentativi, uscita, creazione dell'account e recupero dell'accesso finisce in `admin_audit_log`, con indirizzo e browser.
- **Sessione:** su database, 30 minuti, chiusa con il browser, cifrata; cookie `equo_sessione` non leggibile dagli script, mai mandato da altri siti, solo HTTPS in produzione (`SESSION_SECURE_COOKIE=true`).
- **Browser:** regole di sicurezza (CSP) solo per le risorse del nostro server, nessun font o script esterno, pagine non salvate dal browser.
- **Pagine, tutte in sola lettura:** stato dei dati (prezzi verificati, ore da tempario, tariffe confermate, modelli pronti per il form, richieste ricevute) e copertura per versione; richieste ricevute; impostazioni; registro delle azioni. Modifica delle impostazioni, backup e import dal pannello arrivano con L8.
- I file di stile e script del pannello (`public/css|js|fonts/filament`) si rigenerano a ogni `composer install` e non stanno su git.

## Azioni dell'area riservata (L8, 19/11/2026)

Tutte con conferma, tutte nel registro delle azioni con il valore precedente, tutte con l'utente MySQL `equo_import`.

- **Import dati** (`/gestione/import`): carichi il file Excel (solo `.xlsx`, massimo 10 MB). Parte una **prova**: lo script `database-mysql/import/importa_dati.py` controlla e carica tutto e poi annulla; vedi il report. Se va bene, **confermi**: prima un backup automatico, poi l'import vero, tutto o niente. Un solo import alla volta. Lo script riceve le credenziali in un file temporaneo, cancellato subito dopo, e non accetta file fuori dalla cartella degli import. Sul server servono Python con `openpyxl` e il client `mysql` (`EQUO_PYTHON`, `EQUO_MYSQL`).
- **Impostazioni**: si modificano i valori numerici (IVA, ore minime, soglie del verdetto, scarto, regole della taratura), entro limiti fissati; la soglia "sopra la media" deve restare più bassa di "anomalo".
- **Modelli nel sito** e **Interventi nel sito**: si accendono e spengono. Nascondere non cancella niente.
- **Backup** (`/gestione/backup`): elenco e backup immediato; uno automatico ogni notte alle 02:30 (`routes/console.php`; sul server serve `php artisan schedule:run` ogni minuto). Si tengono 30 giorni (`EQUO_BACKUP_GIORNI`) e almeno gli ultimi 5. I file non si scaricano dal web; il ripristino si fa solo dal server.
- Restano **non modificabili dal pannello**: prezzi, codici, ore, tariffe, ricarichi, fonti. Entrano solo dall'import.
- Prova del 19/11/2026: import di prova del file vero dal servizio Laravel, esito OK, niente salvato (11 versioni, 170 righe di composizione, 89 codici, 73 prezzi, 129 riferimenti, 104 ore, 60 tariffe, 6 ricarichi, 10 valori di mercato).

## Test

```powershell
C:\php85\php.exe artisan test
```

- I test usano solo il database di prova `equo_test` (impostato in `phpunit.xml`); `tests/TestCase.php` li ferma se un collegamento puntasse a `equo`.
- A ogni esecuzione il comando `equo:copia-dati-test` copia i dati veri in `equo_test` (con l'utente `equo_schema`; rifiuta di copiare verso qualunque database diverso da `equo_test`).
- Le scritture di prova (una richiesta, una riga di registro) stanno in una transazione annullata alla fine.
- 16/11/2026, L3: **15 test, tutti verdi**. Dopo L4: **25 test, tutti verdi**. Dopo L5: **42 test, tutti verdi** (API: risposte, 422 sui dati sbagliati, 405 sui metodi non previsti, 429 oltre i limiti di traffico). Dopo L6: **52 test, tutti verdi** (tolti i 2 esempi di Laravel, aggiunti 12 test di sicurezza). Dopo L7: **64 test, tutti verdi** (12 sull'area riservata). Dopo L8: **73 test, tutti verdi** (9 sulle azioni: impostazioni, modelli, interventi, backup, import). Letture e relazioni sui dati veri (Panda: marca, cambio, composizione del tagliando, 1,41 ore da tempario; filtro olio W 610/3 verificato a 8,0615 € netti). Ogni modifica al catalogo rifiutata dal codice e, passando sotto i modelli, da MySQL (errore 1142). Richieste e registro: aggiunta permessa, modifica e cancellazione rifiutate. Dati veri invariati dopo i test.

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

Fatte nel task L5 (16/11/2026):

- **`/api/opzioni`**: `marche` (ognuna con i suoi `modelli`), `interventi` visibili, `qualita` e `tipi_officina` con etichetta, `regioni`. Con `EQUO_SOLO_MODELLI_COMPLETI=true` (produzione) compaiono solo i modelli con `info_completed = 1`: **oggi nessuno dei 10 lo è**, quindi il form resta vuoto finché non si segnano i modelli pronti. In sviluppo il `.env` locale ha `false`.
- **`/api/varianti?modello_id=…&anno=…`**: versioni del modello (con l'anno, solo quelle prodotte in quell'anno) e, per ognuna, `interventi_non_previsti` (frizione con il cambio automatico, cinghia con la catena).
- **`/api/verifica`** (POST, JSON): `variante_id`, `intervento_id` oppure `altro_intervento` (testo), `qualita`, `regione_id`, `tipo_officina`, `importo_preventivo` (fino a 20.000 €, 2 decimali), `iva_inclusa`, `anno_immatricolazione` facoltativo. Risponde con `dettaglio`, `riepilogo`, `verdetto` e `preventivo_iva_inclusa` (se il preventivo è senza IVA si porta al lordo). Salva la richiesta anonima. Con `altro_intervento` salva solo la richiesta, esito `non_calcolabile`. Rifiuta (422, con messaggio in italiano per ogni campo): valori fuori elenco, importi assurdi, interventi non previsti per quella versione, anno fuori produzione. I campi non previsti si ignorano.
- **Errori sempre in JSON**; la rotta `/up` di Laravel è stata tolta.

## Sicurezza, in breve

- Middleware su tre livelli: tutte le richieste (HTTPS, intestazioni di sicurezza, CORS solo dal sito React, limite dimensione), API (solo JSON, solo GET e una POST, limiti di traffico, validazione), area riservata (cookie cifrati, CSRF, limite tentativi, due fattori, ruolo, scadenza sessione, registro azioni).
- Utenti MySQL separati: `equo_api` (solo lettura + inserimento in `quote_requests`), `equo_import` (import e area riservata), `equo_schema` (solo migration). L'API non può modificare i dati nemmeno se il codice venisse compromesso.
- Credenziali solo in `.env` (escluso da git); `APP_DEBUG=false` in produzione; nessun dato personale salvato.

## Piano

I passi L1-L9 sono nel documento di struttura (sezione 10).
