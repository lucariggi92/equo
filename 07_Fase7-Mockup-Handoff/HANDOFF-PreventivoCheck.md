# PreventivoCheck.web — Handoff

## Overview

Web app mobile-first che confronta il preventivo del meccanico con listini medi regionali. Per automobilisti italiani con prezzo in mano. Wizard 4 step + loading + report con semaforo e testo WhatsApp. Stack: React + Tailwind su OpenCode, backend solo webhook n8n in-memory, zero DB.

## Design system

Vedi `06_Fase6-Design-System-Layout/DESIGN.md`. Riutilizzabili: Button primario/secondario + link, Input €, Lista 56px, Card Report, Badge pill, Header 56, Box WhatsApp. Stati in DESIGN.md.

## User flow

1 Regione (1/4) > 2 Auto (2/4) > 3 Intervento (3/4) > 4 Prezzo + Verifica POST > 5 Loading > 6 Report + Copia / Nuova verifica. Lineare, niente rami. Indietro sempre disponibile tranne loading/report.

## Schermate

- 1 Regione: lista 20, Avanti disabled fino a scelta. Stati: nessuna scelta / scelta.
- 2 Auto: search + 10 modelli. Stati: filtro vuoto / nessun risultato.
- 3 Intervento: 5 card. Stati: selezionata con check.
- 4 Prezzo: riepilogo + input € >0 + Verifica. Stati: vuoto/errore <=0 / valido / invio loading.
- 5 Loading: spinner + "Analisi dei listini in corso...". Stati: attesa / errore rete con Riprova.
- 6 Report: semaforo SVG 64 + H1 + pill delta + spaccato Ricambi/Manodopera + box WhatsApp + Copia + Nuova. Stati: 🟢/🟡/🔴.

## Logica di stato

Frontend: `currentStep` 1-6, `{regione, auto, intervento, prezzo}`, `loading/error`, `report {verdetto, delta, media, ricambi, manodopera, messaggio}`. POST JSON a webhook n8n. Backend n8n nodo Code: lookup JSON (20 regioni, 10 auto, 5 interventi) > media complessiva > soglie 🟢 entro media / 🟡 +10-25% / 🔴 oltre +25% > risposta con spaccato + messaggio WhatsApp precompilato. Copia via clipboard con feedback. Nuova verifica resetta tutto, niente persistenza.

## Asset

Da `05_Fase5-Asset/assets/`: `logo.svg` header tutte; `semaforo-verde/giallo/rosso.svg` Report; `spinner.svg` Loading. Copiare in `/public/` o `/src/assets/`.

## Note implementazione

Priorità build: 1 Regione (valida DESIGN) > 2 Auto > 3 Intervento > 5 Loading > 4 Prezzo+POST > 6 Report (più complessa). Mobile-first 360px, italiano. NON costruire: PDF, GPS, targa/API, OEM/Aftermarket, storico/localStorage, login/DB.

### Prompt a strati (OpenCode)

1. Setup: React+Tailwind, token DESIGN.md, componenti Button/Input/Lista/Card/Header/Box, viewport mobile, IT, solo fondamenta.
2. Routing: 6 pagine vuote con header corretto + step.
3. Una schermata alla volta con asset da `/assets`, riusa componenti.
4. Logica: POST n8n, stati loading/errore/report, copia clipboard, reset.
