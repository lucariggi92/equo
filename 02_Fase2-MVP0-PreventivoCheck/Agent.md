# Agent.md — Istruzioni vive del progetto

> Artefatto **vivo**. Aggiornalo a fine di ogni fase. E' il file che incolli a
> qualsiasi AI per farle capire il progetto in 30 secondi.

## Cosa costruisco

PreventivoCheck.web: web app mobile-first con wizard a 4 step + dashboard report che confronta il preventivo del meccanico con listini medi regionali via webhook n8n.

## Per chi

Automobilisti italiani non esperti che hanno già un preventivo in mano e vogliono capire in 30 secondi se è onesto, direttamente dal telefono.

## Problema

Asimmetria informativa sui prezzi di ricambi e manodopera: l'utente non ha un riferimento oggettivo regionale per negoziare o chiedere dettagli al meccanico.

## Stato del progetto

Fase 2 — MVP0 chiusa, pronta Fase 3 Paper Prototype. Scope congelato per 3 sessioni.

## Vincoli

- Scope: costruibile in 3 sessioni (wizard 4 step + report, niente altro).
- Tecnici: React + Tailwind su OpenCode; backend solo n8n webhook con calcolo in-memory in nodo Code (oggetti JSON, 20 regioni, 10 auto, 5 interventi); ZERO database, ZERO LocalStorage.
- Altri: mobile-first, italiano, 1 domanda per schermata, animazione loading "Analisi listini in corso...".

## Stack / Tool

| Fase | Tool | Note |
| --- | --- | --- |
| Ricerca / sintesi | Gemini | prompt di sfida scope creep |
| Wireframe | Stitch / Claude design | solo essenza, da Fase 3 |
| Asset | GenAI immagini | solo se servono icone semaforo |
| Design system / layout | Stitch + DESIGN.md | Tailwind, semaforo 🟢/🟡/🔴 |
| Build / handoff | OpenCode (React) + n8n | POST JSON, risposta con verdetto + spaccato |

## Decisioni ferme

1. Wizard manuale da elenchi preimpostati, niente targa e niente GPS — perche' bastano 2 secondi e zero API/permessi.
2. Calcolo su costo medio complessivo, senza distinzione OEM/Aftermarket — perche' raddoppierebbe JSON e step.
3. Report usa-e-getta senza persistenza — perche' copia WhatsApp basta, niente DB/LocalStorage.
4. Soglie semaforo fisse: 🟢 entro media, 🟡 +10-25%, 🔴 oltre +25% — perche' regola semplice da implementare in n8n.

## No-goal

- Non-goal (mai):
  - Download report in PDF
  - Geolocalizzazione GPS automatica
  - Login / account utente e database
- Post-MVP (dopo, non ora):
  - Ricerca auto via targa con API esterna
  - Distinzione pezzi OEM vs Aftermarket
  - Storico verifiche in LocalStorage

## Verifica rapida

- [x] Un'AI che legge solo questo file capisce cosa costruisco e per chi?
- [x] Riflette la fase corrente (non una superata)?
- [x] No-goal e decisioni ferme sono aggiornati con l'ultima fase chiusa?
