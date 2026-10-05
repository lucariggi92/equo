# Fase 4 — Wireframe compilato — PreventivoCheck.web — 2026-09-10

> Template originale intoccato in `04-wireframe.md`.
> Input da `03_Fase3-Paper-Prototype/03-paper-prototype-PreventivoCheck-compilato.md`.
> Solo struttura/gerarchia/contenuti. Niente colori, font, immagini vere, micro-interazioni.
> Mobile-first 360px. ASCII = segnaposto per Stitch/Figma/carta.

## Registro schermate wireframate

| # | Schermata | Azione principale | Stato |
| --- | --- | --- | --- |
| 1 | Regione | Seleziona 1 regione, Avanti | bozza ok |
| 2 | Auto | Sceglie Marca+Modello, Avanti | bozza ok |
| 3 | Intervento | Seleziona 1 lavoro, Avanti | bozza ok |
| 4 | Prezzo + Verifica | Inserisce €, preme Verifica Preventivo | bozza ok |
| 5 | Loading | Attende (Riprova solo se errore) | bozza ok |
| 6 | Report | Copia messaggio WhatsApp | bozza ok |

Prompt generazione usato (per Stitch/Claude/Figma, 1 per schermata):
> Genera wireframe bassa fedeltà mobile per [nome]. Scopo: [da Fase 3]. Struttura header/contenuto/footer sotto. Azione principale deve saltare all'occhio. Solo grigi e segnaposto, niente colori/font/immagini vere.

## Struttura per schermata

### Wireframe: 1 Regione

Blocchi dall'alto in basso:
1. Header: [logo-txt "PreventivoCheck"] + [step "1/4"]
2. Titolo: [H1 "Qual è la tua Regione?"]
3. Blocco principale: [lista 20 righe: "Regione ___ >"] — scroll verticale
4. Footer sticky: [btn "Avanti" disabilitato fino a selezione] + [link "Indietro"]

```
+------------------+
| logo      [1/4]  |
| Qual è la tua    |
| Regione?         |
| +--------------+ |
| | Regione ___ >| |
| | Regione ___ >| |
| | Regione ___ >| |
| +--------------+ |
| [   Avanti    ]  |
+------------------+
```

Elemento con priorità visiva:
> Lista regioni (tap grande).

Segnaposto: righe lista = rettangoli grigi; bottone = rettangolo con label.

---

### Wireframe: 2 Auto

Blocchi:
1. Header: [logo-txt] + [step "2/4"]
2. Titolo: [H1 "Che auto guidi?"]
3. Blocco principale: [search-txt segnaposto "Cerca..."] + [lista 10 righe: "Marca Modello >"]
4. Footer sticky: [btn "Avanti"] + [link "Indietro"]

```
+------------------+
| logo      [2/4]  |
| Che auto guidi?  |
| [Cerca...      ] |
| +--------------+ |
| | Marca Modello>| |
| | Marca Modello>| |
| +--------------+ |
| [Avanti] [<Ind ] |
+------------------+
```

Priorità visiva:
> Lista 10 auto.

---

### Wireframe: 3 Intervento

Blocchi:
1. Header: [logo-txt] + [step "3/4"]
2. Titolo: [H1 "Che intervento devi fare?"]
3. Blocco principale: [5 card verticali: "Intervento ___ >" + sub-txt "es. freni/tagliando/batteria"]
4. Footer sticky: [btn "Avanti"] + [link "Indietro"]

```
+------------------+
| logo      [3/4]  |
| Che intervento   |
| devi fare?       |
| [Intervento ___>]|
| [Intervento ___>]|
| [Intervento ___>]|
| [Avanti] [<Ind ] |
+------------------+
```

Priorità visiva:
> 5 card intervento.

---

### Wireframe: 4 Prezzo + Verifica

Blocchi:
1. Header: [logo-txt] + [step "4/4"]
2. Titolo: [H1 "Quanto ti ha chiesto il meccanico?"]
3. Riepilogo: [txt "Regione / Auto / Intervento" 3 righe piccole]
4. Blocco principale: [input "€ ______" grande + tastierino numerico]
5. Footer sticky: [btn primario "Verifica Preventivo"] + [link "Indietro"]

```
+------------------+
| logo      [4/4]  |
| Quanto ti ha     |
| chiesto?         |
| reg/auto/lavoro  |
| +--------------+ |
| |  €  ______   | |
| | [1][2][3]    | |
| +--------------+ |
| [Verifica       ]|
+------------------+
```

Priorità visiva:
> Input € + bottone Verifica Preventivo.

---

### Wireframe: 5 Loading

Blocchi:
1. Header: [logo-txt] senza step
2. Blocco principale centrato: [img segnaposto spinner] + [txt "Analisi dei listini in corso..."] + [sub-txt "Confronto regionale..."]
3. Solo errore: [txt-errore] + [btn "Riprova"]

```
+------------------+
| logo             |
|                  |
|     [spinner]    |
| Analisi listini  |
| in corso...      |
|                  |
+------------------+
```

Priorità visiva:
> Testo attesa + spinner.

---

### Wireframe: 6 Report

Blocchi:
1. Header: [logo-txt] + [link "Nuova verifica"]
2. Verdetto: [img segnaposto cerchio semaforo] + [H1 "Prezzo Onesto / Leggermente Caro / Fuori Mercato"] + [txt "+XX € sulla media"]
3. Spaccato: [2 righe: "Ricambi stimati €___" / "Manodopera stimata €___"] + [txt piccola regione/auto/lavoro]
4. Box WhatsApp: [txt-box messaggio precompilato 4-6 righe]
5. Footer sticky: [btn primario "Copia messaggio per il meccanico"] + [btn secondario "Nuova verifica"]

```
+------------------+
| logo  [Nuova >]  |
|   (O) semaforo   |
| VERDETTO ___     |
| +XX € su media   |
| Ricambi €___     |
| Manodopera €___  |
| +--------------+ |
| | msg WhatsApp | |
| | ...copia...  | |
| +--------------+ |
| [Copia messaggio]|
+------------------+
```

Priorità visiva:
> Semaforo + verdetto, poi bottone Copia.

---

## Cosa NON ho messo (di proposito)

- Colori: niente, solo grigi (semaforo = cerchio grigio con label 🟢/🟡/🔴 solo a parole).
- Font: nessuna scelta.
- Immagini vere: solo rettangoli "img"/"spinner"/"semaforo".
- Micro-interazioni/transizioni: niente (arrivano in Fase 6b/7).
- No schermate targa/GPS/PDF/storico/login.

## Aggiorna gli artefatti vivi

- Agent.md: stato "Fase 4 bozza ok", tool wireframe = ASCII qui + Stitch/Figma se vuoi iterare 2-3 giri (max 5 connesse, spezza 6 in 4+2).
- workspace.md blocco suggerito:

```txt
### [Fase 4 — Wireframe] — 2026-09-10

Prompt usato:
> Genera wireframe bassa fedeltà mobile per ciascuna delle 6 schermate Fase 3, solo struttura/gerarchia, azione in evidenza, niente stile.

Output tenuto:
> Registro 6 wireframe + ASCII in 04_Fase4-Wireframe/04-wireframe-PreventivoCheck-compilato.md. Priorità: liste/input/verdetto+copia.

Scartato / da verificare:
> Niente colori/font/immagini. Da verificare con collega senza spiegare: capisce schermata e azione?

Decisione:
> Essenza congelata, pronta per Fase 5 asset parallela e Fase 6 design.
```

## Verifica rapida

- [x] Collega capisce schermata senza spiegare? (titolo + lista/input/verdetto espliciti)
- [x] Azione in evidenza? (Avanti / Verifica / Copia sticky)
- [x] Nessun colore/font/immagine vera?
- [x] Struttura = schede Fase 3?
