# Fase 1 — Scoping compilato — PreventivoCheck.web — 2026-09-10

> Compilazione retrospettiva (Fase 2 già chiusa in `02_Fase2-MVP0-PreventivoCheck/`).
> Template originale intoccato in `01-scoping.md`. Niente numeri inventati.

## 1. Ricerca (sintesi)

- Chi: automobilisti italiani non esperti che hanno già un preventivo del meccanico in mano e non sanno se è onesto. Fatto: target già definito in Fase 2. Ipotesi da verificare: fascia più attiva (neo-patentati vs famiglie vs pendolari).
- Contesto: dal telefono, mobile-first, dopo la visita in officina o via WhatsApp, prima di accettare il lavoro. Fatto: flusso wizard 1 domanda per schermata già validato come scope.
- Soluzioni attuali: passaparola, secondo preventivo in altra officina, ricerca prezzi ricambi online, gruppi/forum auto. Tutte lente o poco confrontabili per regione.
- Alternative: comparatori generici, listini ricambi online, preventivi telefonici multipli. Nessuna dà verdetto immediato 🟢/🟡/🔴 con spaccato regionale e testo negoziazione pronto.

## 2. Evidenziazione

| Insight tenuto | Perche' rilevante | Da verificare? |
| --- | --- | :---: |
| Asimmetria informativa ricambi + manodopera regionale | E' il nucleo: senza riferimento regionale l'utente non può negoziare | no |
| Verdetto in 30 secondi da telefono vale più della precisione assoluta | Giustifica costo medio senza OEM/Aftermarket e senza targa/GPS | no |
| Testo WhatsApp copia/incolla chiude il job | Evita PDF, login, storico: report usa-e-getta basta | sì: tono messaggio da testare con 3-5 utenti |
| Selezione manuale da elenchi basta per MVP | Evita API targhe e permessi GPS, tiene 3 sessioni | no |
| Fiducia nel dato stimato è il rischio principale | Se l'utente non crede alla media, non usa il verdetto | sì: mostrare fonte/intervallo in Fase 3 |

## 3. Raffinazione — assunzioni rischiose

| Assunzione | Rischio se falsa | Come verifico |
| --- | --- | --- |
| L'utente accetta una stima media regionale senza targa esatta | Abbandona wizard perché lo ritiene impreciso | Test 5 utenti: chiedi se la mancanza targa riduce fiducia |
| 10 auto + 5 interventi coprono i casi reali | Utente non trova la sua auto/lavoro e esce | Log delle selezioni "altro / non trovo" in Fase 7 |
| Soglie +10-25% / oltre +25% sono percepite come eque | Semaforo percepito arbitrario | Mostra 3 esempi a utenti e chiedi se cambierebbero soglie |
| Il testo WhatsApp viene davvero copiato e inviato | Report letto e chiuso senza azione | Tasto copia con feedback + domanda follow-up |
| Zero persistenza va bene per uso occasionale | Utente vuole ritrovare vecchi report | Se richiesto, rivaluta storico LocalStorage Post-MVP |

## 4. Sintesi — scheda-scoping

- Problema (1 frase):
  > Gli automobilisti italiani non hanno un riferimento oggettivo e regionale per capire se il preventivo del meccanico è onesto e negoziarlo.
- Target (chi, concreto):
  > Automobilisti non esperti in Italia che hanno già un prezzo in € dal meccanico e usano il telefono prima di accettare.
- Contesto d'uso:
  > Mobile-first, dopo l'officina o via messaggio, wizard di 4 domande + report immediato con semaforo e testo WhatsApp.
- Valore atteso (cosa migliora per l'utente):
  > Decisione in 30 secondi, base negoziale con spaccato Ricambi/Manodopera, meno ansia da fregatura.
- Ipotesi principali:
  > Stima media regionale basta; elenchi manuali bastano; report usa-e-getta senza DB basta; copia WhatsApp è l'azione chiave.
- Rischi / assunzioni da verificare:
  > Fiducia nella stima senza targa [da verificare con 5 test]; copertura 10 auto / 5 interventi [da verificare con log "non trovo"]; soglie semaforo percepite eque [da verificare]; tono messaggio WhatsApp [da verificare]; bisogno storico [Post-MVP, solo se richiesto].

### Agent.md v1 (retrospettivo, superato da Fase 2)

> PreventivoCheck.web: wizard mobile 4 step + report semaforo via n8n per automobilisti italiani con preventivo in mano. Problema: nessuno riferimento regionale oggettivo. Vincoli: 3 sessioni, React+Tailwind OpenCode + n8n in-memory, zero DB. No-goal evidenti: PDF, GPS, login. Vedi versione corrente in `02_Fase2-MVP0-PreventivoCheck/Agent.md`.

## Aggiorna gli artefatti vivi

- [x] Scheda-scoping sopra compilata senza sovrascrivere `01-scoping.md`.
- [x] Agent v1 riportato come snapshot; corrente in Fase 2.
- [x] Blocco workspace sotto da accodare in Fase 2 workspace se serve tracciabilità.

```txt
### [Fase 1 — Scoping retrospettiva] — 2026-09-10

Prompt usato:
> Ricerca problema/target/contesto/alternative per PreventivoCheck.web senza inventare numeri; evidenziazione insight; 5 assunzioni rischiose con verifica semplice.

Output tenuto:
> Scheda-scoping sopra + 5 assunzioni (fiducia stima, copertura elenchi, soglie, copia WhatsApp, bisogno storico).

Scartato / da verificare:
> Scartato secondo preventivo fisico e comparatori generici come soluzioni (lenti/non regionali). Da verificare con utenti: fiducia senza targa, tono WhatsApp, soglie.

Decisione:
> Fase 1 recuperata a posteriori, coerente con Fase 2 congelata. Nessuno scope aggiunto.
```

## Verifica rapida

- [x] Problema in una frase, target concreto?
- [x] Fatti separati da ipotesi con "da verificare"?
- [x] Agent v1 snapshot + rimando a versione corrente Fase 2?
- [x] Prompt incollato per intero sopra?
