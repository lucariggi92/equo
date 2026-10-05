# Fase 4 — Wireframe

> Scopo: dal paper prototype ai **wireframe**. Rappresentano le schermate nella
> loro essenza: struttura, gerarchia, contenuti. **Niente colori, font
> elaborati o immagini vere.** Parallelizzabile con la Fase 5 (asset).

**Input attesi:** mappa schermate + schede schermata (Fase 3).
**Output:** un wireframe per schermata (in Stitch / Claude design / Figma / carta)
+ questo file compilato come registro.
**Tool default:** Stitch o Claude design. Alternativa: Figma, carta.

Nota Stitch: 1 solo progetto, canvas infinita, max ~5 schermate connesse.
L'output e' una bozza: itera 2-3 giri.

---

## Prompt di generazione wireframe

Un wireframe per schermata. Il wireframe rappresenta **contenuto e struttura**,
non lo stile.

```txt
Genera il wireframe a bassa fedelta' di una schermata mobile.
Schermata: [nome].
Scopo: [cosa fa l'utente qui, una frase].
Struttura (dall'alto in basso):
- [zona 1: es. header con logo + azione]
- [zona 2: es. blocco principale, lista di card]
- [zona 3: es. footer / bottom navigation]
Azione principale (deve saltare all'occhio): [es. bottone "Aggiungi"].
Vincoli: solo struttura, gerarchia e contenuti. Niente colori, niente font
         scelti, niente immagini vere: usa rettangoli grigi e segnaposto testo.
         Mobile-first.
```

## Registro schermate wireframate

| # | Schermata | Azione principale | Stato (bozza / iterata / ok) |
| --- | --- | --- | --- |
| 1 | | | |
| 2 | | | |
| 3 | | | |
| 4 | | | |

## Struttura per schermata (nota di composizione)

Duplica per ogni schermata. Serve a rendere esplicita la composizione.

Prompt di supporto (se vuoi aiuto a descrivere i blocchi):
```txt
Ruolo: Sei un designer che descrive la composizione di una schermata a blocchi.
Contesto: Schermata: [nome]. Scopo: [frase]. Contenuti dalla Fase 3: [incolla].
Obiettivo: Elenca i blocchi dall'alto in basso (header, blocco principale,
           footer/nav) e indica quale elemento ha priorita' visiva.
Vincoli: solo struttura e gerarchia, niente stile/colori; un blocco per riga;
         una sola priorita' visiva.
Formato: elenco numerato dei blocchi + riga "Priorita' visiva: ...".
```

### Wireframe: (nome schermata)

Blocchi dall'alto in basso:
1. Header: [cosa contiene]
2. Blocco principale: [cosa contiene]
3. ...
4. Footer / nav: [cosa contiene]

Elemento con priorita' visiva (cosa deve vedere l'utente per primo):
> (compila)

Segnaposto usati:
- Immagini: rettangolo vuoto con etichetta "img".
- Testo variabile: etichetta di esempio ("Nome", "Prezzo").
- Bottoni: rettangolo con etichetta dell'azione.

## Cosa NON ho messo (di proposito)

- Colori: niente, solo grigi.
- Font: nessuna scelta tipografica.
- Immagini vere: niente, solo segnaposto.
- Micro-interazioni: niente.
- Altri:
  -

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: stato aggiornato (Fase 4), tool wireframe annotato.
- [ ] `workspace.md`: accodato blocco Fase 4 con i prompt di generazione usati.

## Verifica rapida

- [ ] Mostrando il wireframe a un collega senza spiegare, capirebbe quale schermata e'?
- [ ] L'azione principale e' visibile al primo sguardo?
- [ ] Nessun colore, font elaborato o immagine vera?
- [ ] La struttura corrisponde alla scheda schermata della Fase 3?
