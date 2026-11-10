# workspace.md — Log del progetto

> Artefatto **append-only**. Non riscrivere: accoda in fondo a ogni passo.

## Regola di ripartenza

Se domani riapro il lavoro, in meno di 30 secondi devo ritrovare: l'ultimo
prompt usato, l'output tenuto, la decisione aperta.

## Come si accoda

Per ogni passo importante aggiungi un blocco con questo schema. Non cancellare i
blocchi vecchi.

```txt
### [Fase N — nome] — AAAA-MM-GG

Prompt usato:
> (incolla il prompt)

Output tenuto (sintesi):
> (cosa hai tenuto)

Scartato / da verificare:
> (cosa hai buttato o marcato "da verificare")

Decisione:
> (cosa hai deciso e perche')
```

---

## Storico

### [Fase 2 — MVP0] — 2026-09-10

Prompt usato:
> RUOLO: SEI UN pm SCETTICO CHE AIUTA A NON far esplodere l'MVP. Obiettivo: dato questo MVP (PreventivoCheck.web: wizard 4 step Regione/Auto/Intervento/Prezzo + report Semaforo via n8n, zero DB), elenca 5 feature che sembrerebbero utili ma che allargherebbero lo scope oltre 3 sessioni. Vincoli: max 5 feature; 1 riga di perche' allargano lo scope; non proporre feature gia' dentro l'MVP. Formato: elenco numerato con Feature e Perche' allarga.

Output tenuto (sintesi):
> 1. Ricerca auto via Targa — richiede API visura esterna + gestione errori. 2. Download report PDF — richiede librerie rendering + file statici. 3. GPS automatico Regione — richiede permessi + reverse geocoding. 4. OEM vs Aftermarket — raddoppia JSON n8n + step wizard. 5. Storico LocalStorage — richiede stato persistente + UI cronologia. Classificazione utente: 1-4-5 Post-MVP, 2-3 Non-Goal.

Scartato / da verificare:
> Scartato PDF e GPS come Non-Goal definitivi (copia WhatsApp e tendina 20 regioni bastano). Rinviati Targa, OEM/Aftermarket, Storico a Post-MVP da rivalutare solo dopo validazione. Aggiunto 3° Non-Goal esplicito: no login/account e no DB, coerente con vincolo zero-database.

Decisione:
> Scope congelato per 3 sessioni OpenCode+n8n: wizard manuale 4 step + calcolo in-memory su costo medio + report usa-e-getta (Semaforo 🟢 entro media / 🟡 +10-25% / 🔴 oltre +25% + spaccato Ricambi/Manodopera + testo WhatsApp). Compilata SCHEDA_MVP_V1/scheda-mvp.md e Agent.md Fase 2 in FASE2_V1/.

---

### [Fase 2 — Nota di passaggio] — 2026-09-10

Prompt usato:
> Gate fine fase: cosa passa alla Fase 3?

Output tenuto (sintesi):
> Passa a Fase 3: definizione MVP 1-frase, tabella no-goal/post-MVP, user flow lineare Regione->Auto->Intervento->Prezzo->Report, mappa 6 schermate.

Scartato / da verificare:
> Niente scope creep aggiunto. Da verificare in Fase 3: asset minimi per Report (icone semaforo, formattazione spaccato).

Decisione:
> Fase 2 chiusa. Prossimo: Fase 3 Paper Prototype da SCHEDA_MVP_V1 + FASE2_V1/Agent.md.

---

## Verifica rapida

- [x] Ogni fase chiusa ha almeno un blocco qui?
- [x] I prompt sono incollati per intero (riproducibili), non riassunti?
- [x] Ho accodato, non riscritto o cancellato blocchi precedenti?
