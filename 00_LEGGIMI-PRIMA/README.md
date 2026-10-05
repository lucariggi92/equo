# Workflow Progetto AI-first

Una pipeline di **7 fasi** per portare un'idea a un design pronto per il build,
usando l'AI a ogni passo. Pensata per lo studente che lavora su un proprio
progetto end-to-end. Ogni fase produce un artefatto che alimenta la successiva.

Principio: **decisioni prima di pixel, sistema prima di schermate, contesto prima
di codice.**

## I due artefatti vivi

Due file accompagnano tutte le fasi e sono il contesto portatile tra tool diversi:

- **`Agent.md`** — istruzioni *vive* del progetto: cosa costruisci, per chi,
  vincoli, stack, decisioni ferme, no-goal. Corto e **corrente** (si sovrascrive).
  E' il file che incolli a qualsiasi AI per farle capire il progetto in 30 secondi.
- **`workspace.md`** — log **append-only**: storico dei prompt usati, output
  tenuti/scartati, decisioni con motivo. E' la memoria e la tracciabilita'.

Regola: a fine di ogni fase, **aggiorna `Agent.md`** e **accoda a `workspace.md`**.

## Il flusso

```
Fase 1  Scoping ───────── Agent.md v1, workspace.md, scheda-scoping
   │
Fase 2  MVP0 ──────────── feature essenziali, no-goal/post-MVP, flusso discorsivo
   │
Fase 3  Paper Prototype ─ flusso schermate + scopo/azione/asset per schermata
   │
   ├────────────┬───────────────
   ▼            ▼
Fase 4        Fase 5        (parallele)
Wireframe     Asset
solo essenza  gen AI + registro
   │            │
   └─────┬──────┘
         ▼
Fase 6a  Design System ── DESIGN.md (hex, tipografia, componenti+stati)
         │
Fase 6b  Layout ───────── redesign vero + coerenza cross-screen
         │
Fase 7   Mockup/Handoff ─ screenshot finali + handoff doc ─▶ dev / Lovable / Figma Make
```

## Le fasi in tabella

| # | Fase | Input | Attivita' | Output | Tool default |
| --- | --- | --- | --- | --- | --- |
| 1 | Scoping | idea grezza | ricerca → evidenziazione → raffinazione → sintesi | `Agent.md`, `workspace.md`, scheda-scoping | Gemini / Claude |
| 2 | MVP0 | scoping | feature essenziali, no-goal vs post-MVP, flusso discorsivo | `02-mvp0` compilato | Gemini / Claude |
| 3 | Paper prototype | MVP0 | flusso schermate + scheda per schermata | `03-paper-prototype` | carta / LLM |
| 4 | Wireframe | paper prototype | wireframe a bassa fedelta' (solo essenza) | wireframe + registro | Stitch / Claude design |
| 5 | Asset | brand + schermate | genera asset AI + registro | asset + registro | tool GenAI immagini |
| 6a | Design system | brand + asset | `DESIGN.md`: palette hex, tipografia, componenti | `DESIGN.md` | Stitch (DESIGN.md) |
| 6b | Layout | wireframe + DESIGN.md + asset | redesign vero + coerenza cross-screen | layout per schermata | Stitch / Claude design |
| 7 | Mockup/Handoff | layout + DESIGN.md | mockup hi-fi + doc handoff | screenshot + handoff | AI multimodale + Lovable |

Le fasi 4 e 5 sono **parallelizzabili**: i wireframe dicono *dove* vanno gli
asset, la fase asset li *produce*.

## Come si usa

1. Copia la cartella `templates/` nel tuo progetto (o duplica i file che ti servono).
2. Parti dalla Fase 1: compila `01-scoping.md`, poi genera `Agent.md` e apri `workspace.md`.
3. Procedi una fase alla volta. Alla fine di ognuna passa dal gate
   (`checklist/gate-fine-fase.md`) prima di andare avanti.
4. Tieni `Agent.md` e `workspace.md` sempre aggiornati: sono il contesto che
   passi a ogni AI.

## Politica strumenti (BYO tool)

I tool indicati sono **default consigliati**, non obbligatori. Puoi sostituirli
(altro LLM, altro tool di design, altra piattaforma di build) mantenendo lo
stesso flusso e gli stessi artefatti. Annota in `Agent.md` quale tool usi per
ogni fase.

## Contenuto della cartella

```
workflow-progetto/
├── README.md                       # questo file
├── templates/
│   ├── Agent.md                    # artefatto vivo: istruzioni progetto
│   ├── workspace.md                # artefatto vivo: log append-only
│   ├── 01-scoping.md
│   ├── 02-mvp0.md
│   ├── 03-paper-prototype.md
│   ├── 04-wireframe.md
│   ├── 05-asset.md
│   ├── 06a-design-system.md        # produce DESIGN.md di progetto
│   ├── 06b-layout.md
│   └── 07-mockup-handoff.md
└── checklist/
    └── gate-fine-fase.md
```
