# Frontend React

App web mobile-first di EQUO: React 19 + Vite + Tailwind CSS. Otto schermate: benvenuto, regione, auto (marca → modello → versione, anno facoltativo), intervento (pacchetti e pezzi singoli, o descritto a parole), ricambi e officina, prezzo con IVA, attesa, report con verdetto.

**Stato:** stile della V1, collegata all'API di [`../backend-laravel`](../backend-laravel/README.md) (fase React del pilota, passi R2-R7). Tutti gli elenchi arrivano dall'API; nessun dato è scritto nel sito.

## Avvio

Requisiti: Node.js 20+ e l'API Laravel avviata (`C:\php85\php.exe artisan serve` in `backend-laravel`, porta 8000).

```bash
npm install
npm run dev
```

L'app si apre su http://localhost:5173. Per fermarla: Ctrl + C nel terminale.

Altri comandi: `npm run build` (versione di produzione in `dist/`), `npm run lint`, `npm run preview`.

## Configurazione

`VITE_API_URL` è l'indirizzo dell'API. In sviluppo può restare vuoto: il sito usa http://localhost:8000/api. Online va impostato (copia `.env.example` in `.env`) e l'indirizzo del sito va messo in `FRONTEND_URL` del backend, altrimenti il browser blocca le risposte. `.env` non va su git.

Se l'API non risponde, il sito lo dice e mostra il pulsante Riprova; gli errori sui dati arrivano dall'API già in italiano.

## Struttura

| Cartella | Contenuto |
|---|---|
| `src/screens/` | le 8 schermate |
| `src/components/` | componenti riusabili (Button, Keypad, Semaforo, StickyFooter…) |
| `src/api/` | `client.js`: le tre chiamate all'API Laravel (opzioni, varianti, verifica) e i messaggi di errore; `verifica.js`: testo del messaggio per il meccanico |
| `src/lib/` | formattazione (euro, percentuali, date, nome dell'auto) e copia del messaggio |

## Report

Mostra il verdetto dell'API (equo, sopra la media, anomalo) oppure "dati insufficienti" quando manca un prezzo: in quel caso niente verdetto, l'elenco di cosa manca e la stima indicata come parziale. Poi lo spaccato (ricambi, ricarico, manodopera con ore e tariffa, IVA, forbice), i ricambi considerati con l'affidabilità di ognuno (verificato, indicativo, stimato), la data dei prezzi e il messaggio per il meccanico. Il preventivo senza IVA viene portato al lordo dall'API prima del confronto.

