# Fase 3 — Paper Prototype

> Scopo: trasformare il flusso discorsivo (Fase 2) in un **flusso di schermate**
> e definire, per ogni schermata, **scopo**, **azione dell'utente** e **asset**
> che conterra'. Ancora a bassissima fedelta': testo o carta, nessun layout.

**Input attesi:** flusso base discorsivo e MVP0 (Fase 2).
**Output:** questo file compilato (mappa schermate + una scheda per schermata).
**Tool default:** carta, o un LLM per derivare la mappa dal flusso discorsivo.

---

## Dal discorsivo alle schermate

Prompt (opzionale, per derivare la mappa dal flusso):
```txt
Ruolo: Sei un product designer che traduce un flusso d'uso in schermate.
Contesto: Ecco il flusso base discorsivo del mio MVP0: [incolla il flusso].
          No-goal: [incolla i no-goal].
Obiettivo: Elenca il numero minimo di schermate che coprono questo flusso,
           in ordine di navigazione. Per ognuna: nome, scopo in una frase,
           azione principale dell'utente.
Vincoli: numero minimo di schermate; niente schermate per feature no-goal;
         una sola azione principale per schermata.
Formato: elenco ordinato Schermata -> Scopo -> Azione principale.
```

## Flusso delle schermate

Il percorso di navigazione. Una linea, dal primo contatto all'esito.

```txt
[Schermata 1] -> [Schermata 2] -> [Schermata 3] -> [Schermata 4]
```

| # | Schermata | Scopo (1 frase) | Porta a |
| --- | --- | --- | --- |
| 1 | | | |
| 2 | | | |
| 3 | | | |
| 4 | | | |

## Scheda per schermata

Duplica questo blocco per ogni schermata del flusso.

Prompt di supporto (se vuoi aiuto a dettagliare una schermata):
```txt
Ruolo: Sei un product designer che definisce lo scopo delle schermate.
Contesto: Flusso schermate: [incolla la mappa]. Schermata da dettagliare: [nome].
          MVP0 e no-goal: [incolla].
Obiettivo: Per questa schermata proponi scopo in una frase, unica azione
           principale dell'utente, e la lista di asset/contenuti che mostra.
Vincoli: una sola azione principale; scopo in una frase; solo contenuti coerenti
         con l'MVP0; niente schermate per feature no-goal.
Formato: Scopo / Azione principale / Asset (elenco tipo+nota).
```

### Schermata: (nome)

- **Scopo** — cosa serve questa schermata, in una frase:
  > (compila)
- **Azione dell'utente** — la cosa principale che l'utente fa qui:
  > (compila)
- **Asset / contenuti** — dati e media che la schermata mostra (anche solo a
  parole; gli asset veri arrivano in Fase 5):

  | Elemento | Tipo (testo / immagine / lista / bottone / input) | Nota |
  | --- | --- | --- |
  | | | |
  | | | |
  | | | |

- **Navigazione in uscita** — dove si va da qui:
  > (compila)

---

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: aggiornato lo stato (Fase 3) e, se emerse, decisioni sul flusso.
- [ ] `workspace.md`: accodato blocco Fase 3 (mappa schermate + eventuale prompt).

## Verifica rapida

- [ ] Ogni schermata ha uno scopo in una frase? (Se non ce l'ha, non serve.)
- [ ] Ogni schermata ha una sola azione principale?
- [ ] Il flusso e' lineare e copre l'MVP0 senza schermate per i no-goal?
- [ ] Per ogni schermata so quali asset/contenuti conterra'?
