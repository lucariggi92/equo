# Fase 5 — Asset compilato — PreventivoCheck.web — 2026-09-10

> Template originale intoccato in `05-asset.md`.
> MVP utilitario: asset ridotti al minimo (YAGNI). Niente hero image, niente illustrazioni.

## Direzione di brand

- Mood (3-4 aggettivi):
  > diretto, affidabile, essenziale, rassicurante
- Palette di riferimento (hex esatti in Fase 6a, qui indicazioni):
  > neutri scuri per testo (#111827), grigio bordi (#E5E7EB), semaforo 🟢 #16A34A / 🟡 #EAB308 / 🔴 #DC2626, bottone primario scuro. Sfondo bianco.
- Cosa evitare:
  > foto officine realistiche, gradienti aggressivi, icone 3D, font giocosi — fuori tono fiducia/prezzo.

## Prompt di generazione asset (pronti se vuoi GenAI, ma non servono)

```txt
Genera icona piatta minimal per app verifica preventivi meccanico.
Mood: diretto, affidabile, essenziale.
Uso: cerchio semaforo 64px nel Report.
Vincoli: piatto, senza testo, verde #16A34A pieno su trasparente.
Formato: SVG quadrato 1:1. Stesso stile delle altre 2 icone semaforo.
```

> Nota: per questo MVP gli SVG sotto bastano, fatti a codice. GenAI solo se vuoi logo illustrato in futuro (Post-MVP).

## Registro asset

| Asset | Uso previsto | Schermata target | Tool | Coerente? | Stato |
| --- | --- | --- | --- | :---: | --- |
| `assets/logo-equo-dark.svg` | Wordmark EQUO bianco header dark | Tutte (header) | codice SVG testo `#FFFFFF` | sì | ok (sostituisce `logo.svg` nero light obsoleto, rimosso 2026-09-12) |
| `assets/semaforo-verde.svg` | Cerchio verdetto onesto | 6 Report | codice SVG | sì | ok |
| `assets/semaforo-giallo.svg` | Cerchio leggermente caro | 6 Report | codice SVG | sì | ok |
| `assets/semaforo-rosso.svg` | Cerchio fuori mercato | 6 Report | codice SVG | sì | ok |
| `assets/spinner.svg` | Attesa calcolo | 5 Loading | codice SVG | sì | ok |

Convenzione naming/posizione:
> `05_Fase5-Asset/assets/*.svg` ora; in build spostare in `/src/assets/` o `/public/` React (vedi Fase 7). Nomi kebab-case, solo SVG per semaforo/spinner/logo.

## Cosa NON ho generato (di proposito)

- Asset per no-goal: niente PDF icon, niente GPS/pin, niente avatar login, niente thumbnails storico.
- Hero/illustrazioni officina: scartate, il wizard non ne ha bisogno e rallentano mobile.
- Varianti stilistiche: scartato 3D/gradienti/foto.

## Aggiorna gli artefatti vivi

- Agent.md: mood diretto/affidabile/essenziale, asset in `05_Fase5-Asset/assets/`.
- workspace.md blocco suggerito:

```txt
### [Fase 5 — Asset] — 2026-09-10

Prompt usato:
> Icona piatta minimal semaforo 64px, mood diretto/affidabile, piatto senza testo, SVG 1:1.

Output tenuto:
> 5 SVG codice: logo + 3 semafori + spinner in 05_Fase5-Asset/assets/. Registro sopra.

Scartato:
> Hero/foto officina, icone PDF/GPS/login/storico (no-goal).

Decisione:
> Solo asset essenziali, pronti per Fase 6.
```

## Verifica rapida

- [x] Stesso mood/stile? (piatto, monocromo + semaforo)
- [x] Ogni asset ha uso + schermata?
- [x] Nessun asset per no-goal?
- [x] Nominati in `assets/` ritrovabili in Fase 6?
