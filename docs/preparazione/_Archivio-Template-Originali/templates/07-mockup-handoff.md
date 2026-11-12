# Fase 7 — Mockup / Handoff

> Scopo: dai layout finali produrre il **mockup ad alta fedelta'** e il
> **documento di handoff** per passare il progetto a un developer o a una
> piattaforma di generazione (Lovable / Figma Make / v0).
> Il `DESIGN.md` copre i token; l'handoff aggiunge cio' che un'immagine non
> mostra: user flow, funzione delle schermate, logica di stato, asset.

**Input attesi:** layout finali (Fase 6b), `DESIGN.md` (Fase 6a), asset (Fase 5).
**Output:** screenshot finali esportati + documento di handoff + questo file.
**Tool default:** AI multimodale (Gemini/Claude) per generare l'handoff; Lovable
per il build.

Regola: usa gli **screenshot finali** (post-redesign, non i wireframe) + il
`DESIGN.md`. Esplicita a parole le cose invisibili in immagine (logica di stato,
regole di business, stati vuoti).

---

## 1. Genera il documento di handoff

Dai all'AI gli screenshot finali + il `DESIGN.md`.

```txt
Sei un product designer/engineer. Ti do gli screenshot finali di un'app mobile
e il suo DESIGN.md (design system). Genera un documento di HANDOFF in markdown
per ricostruire fedelmente l'app in [Lovable / Figma Make / v0].

INPUT:
- [allega gli screenshot delle schermate + stati]
- [allega o incolla il DESIGN.md]

REGOLE:
- Scrivi in italiano.
- Descrivi FUNZIONE e COMPORTAMENTO, non ripetere i token del DESIGN.md
  (rimandaci e basta).
- Preciso ma conciso: brief operativo, non un romanzo.
- Niente em dash.

STRUTTURA:

# [Nome App] — Handoff

## Overview
Cosa fa l'app, per chi, in 2-3 righe.

## Design system
Rimanda al DESIGN.md. Elenca solo i componenti riutilizzabili ricorrenti
(Button, Card, Input, BottomNav, Header) con i loro stati.

## User flow
Percorso completo con le azioni che collegano ogni schermata alla successiva.

## Schermate
Per ciascuna, leggendola dallo screenshot:
- Scopo
- Struttura/layout (sezioni dall'alto in basso)
- Componenti usati
- Stati previsti (vuoto/pieno, form valido/errore, loading)
- Navigazione in uscita

## Logica di stato
[descrivi la logica: es. carrello add/remove, calcolo totale, generazione ID
ordine, stato conferma].

## Asset
Immagini presenti e dove vanno usate.

## Note per l'implementazione
Priorita' di build (dalla schermata piu' semplice alla piu' complessa),
mobile-first, lingua UI italiano.
```

Nota: se non ti serve un artefatto riusabile/revisionabile, puoi saltare il doc e
dare **screenshot + `DESIGN.md` direttamente** alla piattaforma di build. Il doc
conviene quando vuoi piu' round o un riferimento condivisibile.

## 2. Nota di handoff (sintesi da compilare)

Anche se generi il doc con l'AI, compila questa sintesi come indice.

Prompt di supporto (se vuoi aiuto a ordinare il build):
```txt
Ruolo: Sei un tech lead che pianifica l'ordine di build.
Contesto: Schermate: [elenco]. Complessita' note (se sai): [note].
Obiettivo: Ordina le schermate dalla piu' semplice alla piu' complessa e indica
           per ognuna cosa la rende semplice o complessa. Elenca i vincoli/no-goal
           come "cosa non costruire".
Vincoli: la prima deve validare il design system; isola le schermate complesse
         alla fine; niente feature no-goal.
Formato: elenco ordinato Schermata | Perche' in questa posizione.
```

- Schermate da costruire (in ordine di priorita', dalla piu' semplice):
  1.
  2.
  3.
- Componenti riutilizzabili: > (compila)
- Asset da usare e dove: > (compila)
- Logica di stato principale: > (compila)
- Vincoli / cosa NON costruire (dai no-goal): > (compila)

## 3. Build a strati (opzionale, se costruisci in Lovable)

Niente mega-prompt. Parti dalla schermata piu' semplice (valida il design
system), poi sali alla piu' complessa (errore isolato).

Prompt 1 — setup + design system:
```txt
Crea un'app mobile React con Tailwind. Prima imposta il design system:
COLORI: [token da DESIGN.md]
TIPOGRAFIA: [font + scala]
COMPONENTI riutilizzabili: Button (primary/secondary/ghost + stati), Input,
Card, BottomNav.
Mobile-first, viewport smartphone. Lingua UI: italiano.
Non creare ancora le schermate: solo fondamenta e componenti.
```

Prompt 2 — navigazione:
```txt
Aggiungi il routing tra le schermate: [lista].
Bottom navigation con [voci]. Crea le pagine vuote con solo l'header corretto.
```

Prompt 3..N — una schermata alla volta (allega screenshot):
```txt
Implementa la schermata [NOME] secondo l'immagine allegata.
- [elementi chiave]
- Usa le immagini in /assets
Riusa i componenti del design system, non crearne di nuovi.
```

Prompt finale — logica di stato:
```txt
Collega la logica: [add al carrello, calcolo totale, generazione ID ordine
#XX-XXXX mostrato in Conferma, ecc.].
```

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: stato finale (Fase 7), link a handoff doc e screenshot.
- [ ] `workspace.md`: accodato blocco Fase 7 con prompt handoff e note di build.

## Verifica rapida

- [ ] Ho usato gli screenshot FINALI (non i wireframe)?
- [ ] L'handoff descrive funzione/comportamento e non ripete i token del DESIGN.md?
- [ ] Sono esplicitati user flow, logica di stato e stati vuoti (invisibili in immagine)?
- [ ] La priorita' di build va dal semplice al complesso?
- [ ] I vincoli/no-goal sono passati (cosa NON costruire)?
