# Frontend React

App web mobile-first di EQUO: React 19 + Vite + Tailwind CSS. Sette schermate: benvenuto, regione, auto, intervento, prezzo, attesa, report con verdetto.

**Stato:** versione 1. Oggi chiede la verifica a un webhook n8n (o usa un mock locale se il webhook non è impostato). Nella fase React del pilota passerà alle API di [`../backend-laravel`](../backend-laravel/README.md).

## Avvio

Requisiti: Node.js 20+.

```bash
npm install
npm run dev
```

L'app si apre su http://localhost:5173. Per fermarla: Ctrl + C nel terminale.

Altri comandi: `npm run build` (versione di produzione in `dist/`), `npm run lint`, `npm run preview`.

## Configurazione

Copia `.env.example` in `.env` e imposta `VITE_N8N_VERIFICA_URL`. Se resta vuoto, l'app usa un mock locale (solo per sviluppo). `.env` non va su git.

## Struttura

| Cartella | Contenuto |
|---|---|
| `src/screens/` | le 7 schermate |
| `src/components/` | componenti riusabili (Button, Keypad, Semaforo, StickyFooter…) |
| `src/data/` | regioni, auto e interventi della v1 (dati statici) |
| `src/api/` | chiamate al backend (`verifica.js`); qui arriveranno le API Laravel |
| `src/lib/` | formattazione e utilità |
