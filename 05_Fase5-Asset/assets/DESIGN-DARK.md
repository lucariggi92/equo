# EQUO — Palette Dark derivata da logo — V1 — 2026-09-12

> Nuova cartella, originali intoccati. Sorgenti: `12-Definizione logo e palette/1.jpg` (logo) + `2.png` (palette).
> Ex PreventivoCheck, ora `equo`. Schematic dark richiesto. Solo hex, derivati dal logo/palette.

## Analisi JPEG

**1.jpg — Logo dark:**
- Wordmark `EQUO` maiuscolo bianco `#FFFFFF`, su fondo notte scuro
- Label sopra `MOBILE-FIRST BRAND IDENTITY • DARK MODE` in verde mint (stesso accento palette)
- Sottotitolo grigio-blu chiaro (testo secondario)
- Mood: notturno, tecnico, fiducia. Niente blu primario sul logo: logo = bianco, colore = accento mint + fondo.

**2.png — Palette (Dark UI):**
- Deep Night Base `#0B0F19` — sfondo pagina
- Steel Blue `#1A365D` — superfici/card
- Equo Mint (Accento) `#38A169` — CTA + accento + success

## DESIGN-DARK — token finali

| Ruolo | Hex | Derivazione |
| --- | --- | --- |
| Sfondo pagina | `#0B0F19` | Deep Night Base dal PNG |
| Superficie card | `#1A365D` | Steel Blue dal PNG |
| Superficie bassa (header/footer) | `#0B0F19` | stesso sfondo + bordo top/bottom |
| Bordo strutturale | `#2D4A7A` | Steel Blue schiarito per contrasto su dark |
| Testo primario | `#FFFFFF` | bianco logo 1.jpg |
| Testo secondario | `#A0AEC0` | sottotitolo 1.jpg |
| CTA primaria (Verifica / Copia / Avanti) | `#38A169` | Equo Mint, testo `#0B0F19` |
| CTA hover / pressed / disabled | `#2F855A` / `#276749` / `#2D3748` testo `#718096` | shade del Mint |
| Focus ring / link | `#38A169` | stesso Mint, 2px (niente `#2563EB` qui: fuori palette dark) |
| Success (verdetto onesto) | `#38A169` | = Mint — distinguere da CTA con pill outline + icona, mai bottone + semaforo vicini |
| Warning | `#ECC94B` | adattato per leggibilità su dark (giallo semaforo originale `#EAB308` troppo spento su `#0B0F19`) |
| Error | `#FC8181` | adattato per dark (rosso originale `#DC2626` troppo scuro su `#0B0F19`) |
| Input field | fondo `#1A365D`, bordo `#2D4A7A`, focus bordo `#38A169` | dal sistema |

Font: system-ui (come `11_Rinomina-Equo-V1/logo-equo.svg:1`), scala display 28 / h1 22 / h2 17 / body 16 / caption 13.
Radius: card 12 / bottoni-input 10 / pill 999. Spacing 4/8/12/16/24/32, margini 16, header 56, footer 64, viewport 360.

## Cosa NON è dal logo (dichiarato)

- Warning/Error tints per dark: funzionali, non nel PNG. Necessari per contrasto WCAG su `#0B0F19`.
- Hover/pressed/disabled: shade calcolati del Mint, non nel PNG.

## Verifica

- [ ] Tutti hex, nessun nome colore?
- [ ] CTA Mint con testo `#0B0F19` leggibile?
- [ ] Semaforo distinguibile dalla CTA (pill + icona, non solo colore)?
- [ ] Header logo bianco su `#0B0F19`?
