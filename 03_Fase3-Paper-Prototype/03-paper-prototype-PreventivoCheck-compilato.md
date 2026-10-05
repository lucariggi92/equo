# Fase 3 — Paper Prototype compilato — PreventivoCheck.web — 2026-09-10

> Template originale intoccato in `03-paper-prototype.md`.
> Input da `02_Fase2-MVP0-PreventivoCheck/` (MVP 1-frase, flow, no-goal). Nessuna schermata per no-goal (PDF, GPS, login, storico, targa).

## Flusso delle schermate

```txt
[1 Regione] -> [2 Auto] -> [3 Intervento] -> [4 Prezzo + Verifica] -> [5 Loading] -> [6 Report]
```

| # | Schermata | Scopo (1 frase) | Porta a |
| --- | --- | --- | --- |
| 1 | Regione | Contestualizzare la tariffa manodopera su base regionale. | 2 Auto |
| 2 | Auto (Marca+Modello) | Identificare il costo ricambi indicativo tra 10 modelli diffusi. | 3 Intervento |
| 3 | Intervento | Identificare 1 dei 5 lavori comuni da stimare. | 4 Prezzo |
| 4 | Prezzo + Verifica | Raccogliere il prezzo del meccanico e lanciare la POST a n8n. | 5 Loading |
| 5 | Loading "Analisi listini" | Dare feedback durante il calcolo backend. | 6 Report |
| 6 | Report (Semaforo + Spaccato + WhatsApp) | Mostrare verdetto, costi stimati e azione di negoziazione. | Fine (ricomincia) |

## Scheda per schermata

### Schermata: 1 Regione

- **Scopo:**
  > Contestualizzare la tariffa manodopera su base regionale.
- **Azione dell'utente:**
  > Seleziona 1 regione da elenco 20 voci.
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Titolo "Qual è la tua Regione?" | testo | 1 domanda per schermata |
  | Elenco 20 regioni | lista / select | manuale, niente GPS per scelta |
  | Bottone Avanti | bottone | disabilitato finché non seleziona |
  | Step indicator 1/4 | testo | orientamento wizard |

- **Navigazione in uscita:**
  > Avanti a 2 Auto; Indietro esce.

---

### Schermata: 2 Auto (Marca+Modello)

- **Scopo:**
  > Identificare il costo ricambi indicativo tra 10 modelli diffusi.
- **Azione dell'utente:**
  > Sceglie Marca e Modello da elenco preimpostato (es. Fiat Panda, VW Golf, Lancia Ypsilon).
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Titolo "Che auto guidi?" | testo | |
  | Lista 10 auto | lista | niente targa/API per scelta |
  | Bottone Avanti / Indietro | bottone | |
  | Step indicator 2/4 | testo | |

- **Navigazione in uscita:**
  > Avanti a 3 Intervento; Indietro a 1 Regione.

---

### Schermata: 3 Intervento

- **Scopo:**
  > Identificare 1 dei 5 lavori comuni da stimare.
- **Azione dell'utente:**
  > Seleziona 1 intervento (es. Pastiglie+Dischi, Tagliando, Batteria).
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Titolo "Che intervento devi fare?" | testo | |
  | Lista 5 interventi | lista | niente distinzione OEM/Aftermarket |
  | Bottone Avanti / Indietro | bottone | |
  | Step indicator 3/4 | testo | |

- **Navigazione in uscita:**
  > Avanti a 4 Prezzo; Indietro a 2 Auto.

---

### Schermata: 4 Prezzo + Verifica

- **Scopo:**
  > Raccogliere il prezzo del meccanico e lanciare la POST a n8n.
- **Azione dell'utente:**
  > Inserisce importo in € e preme "Verifica Preventivo".
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Titolo "Quanto ti ha chiesto il meccanico?" | testo | |
  | Input numerico € + tastierino grande | input | validazione >0, mobile-first |
  | Bottone "Verifica Preventivo" | bottone | CTA principale, lancia fetch POST JSON {regione, auto, intervento, prezzo} |
  | Riepilogo scelte | testo | regione/auto/intervento per conferma |
  | Step indicator 4/4 | testo | |

- **Navigazione in uscita:**
  > Verifica a 5 Loading; Indietro a 3 Intervento.

---

### Schermata: 5 Loading "Analisi listini"

- **Scopo:**
  > Dare feedback durante il calcolo backend.
- **Azione dell'utente:**
  > Attende (nessun input, solo attesa).
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Animazione / spinner | immagine | testo "Analisi dei listini in corso..." |
  | Stato richiesta | testo | gestione errore rete con Riprova |

- **Navigazione in uscita:**
  > Automatica a 6 Report a risposta n8n; errore resta qui con Riprova.

---

### Schermata: 6 Report (Semaforo + Spaccato + WhatsApp)

- **Scopo:**
  > Mostrare verdetto, costi stimati e azione di negoziazione.
- **Azione dell'utente:**
  > Legge il semaforo e copia il messaggio WhatsApp.
- **Asset / contenuti:**

  | Elemento | Tipo | Nota |
  | --- | --- | --- |
  | Semaforo grande 🟢/🟡/🔴 | testo / immagine | 🟢 Onesto entro media, 🟡 +10-25%, 🔴 oltre +25% |
  | Label verdetto + delta € | testo | es. "Leggermente caro: +35 € sulla media" |
  | Spaccato costi: Ricambi vs Manodopera | lista | valori stimati regionali da n8n, niente PDF |
  | Box testo WhatsApp pronto | testo | precompilato con dati caso |
  | Bottone "Copia messaggio per il meccanico" | bottone | copia clipboard + feedback |
  | Bottone "Nuova verifica" | bottone | reset wizard, niente storico per scelta |

- **Navigazione in uscita:**
  > Nuova verifica a 1 Regione; fine, niente persistenza.

---

## Aggiorna gli artefatti vivi

- Agent.md: stato Fase 3 chiusa, nessuna decisione nuova oltre flusso 6 schermate sopra (da copiare in Agent corrente in nuova versione se serve).
- workspace.md blocco suggerito:

```txt
### [Fase 3 — Paper Prototype] — 2026-09-10

Prompt usato:
> Traduci flusso Fase 2 PreventivoCheck in minimo schermate con scopo/azione/asset, niente schermate per no-goal, 1 azione per schermata.

Output tenuto:
> Mappa 6 schermate Regione->Auto->Intervento->Prezzo->Loading->Report + schede in 03_Fase3-Paper-Prototype/03-paper-prototype-PreventivoCheck-compilato.md.

Scartato / da verificare:
> Niente schermate targa/GPS/PDF/storico/login. Da verificare in Fase 4: formato spaccato e lunghezza testo WhatsApp su mobile.

Decisione:
> Flusso lineare congelato, pronto per wireframe.
```

## Verifica rapida

- [x] Ogni schermata ha scopo in 1 frase?
- [x] Ogni schermata ha 1 sola azione principale?
- [x] Flusso lineare senza schermate per no-goal?
- [x] Asset per schermata noti (dettagli UI in Fase 4/6)?
