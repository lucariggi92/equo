# Prompt Figma — Wireframe essenziali PreventivoCheck.web (MVP0)

> Incolla in Figma Make / Figma AI. Genera 6 frame mobile 360px in bassa fedeltà. Riferimenti: `04-wireframe-PreventivoCheck-compilato.md` (struttura), `03-paper-prototype-PreventivoCheck-compilato.md` (scopo/azioni). Niente DESIGN.md qui: solo grigi.

```txt
Genera 6 wireframe mobile a bassa fedeltà (frame 360x800) per "PreventivoCheck.web", wizard verifica preventivi meccanico. Solo struttura gerarchia contenuti, rettangoli grigi + testo segnaposto, niente colori, niente font scelti, niente immagini vere, niente micro-interazioni. Header 56px identico (logo-txt sx + step dx), footer sticky con CTA, margini 16px. Italiano.

1. Regione — Scopo: tariffa per regione. H1 "Qual è la tua Regione?", lista 20 righe 56px "Regione ___ >", footer Avanti disabled.
2. Auto — Scopo: ricambi tra 10 modelli. H1 "Che auto guidi?", search "Cerca...", lista 10 "Marca Modello >", footer Avanti/Indietro, step 2/4.
3. Intervento — Scopo: 1 di 5 lavori. H1 "Che intervento devi fare?", 5 card 72px "Intervento ___ >", footer Avanti/Indietro, step 3/4.
4. Prezzo + Verifica — Scopo: raccoglie € e lancia POST. H1 "Quanto ti ha chiesto il meccanico?", riepilogo 3 righe, input € grande + tastierino, CTA "Verifica Preventivo" full-width, step 4/4.
5. Loading — Scopo: feedback calcolo. Spinner centro + "Analisi dei listini in corso...", stato errore con Riprova.
6. Report — Scopo: verdetto + azione. Cerchio semaforo grigio + H1 verdetto + pill "+XX €", 2 righe spaccato, box messaggio, CTA "Copia messaggio per il meccanico" + "Nuova verifica".

Collega: 1>2>3>4>5>6. Azione principale in evidenza per frame. Escludi: PDF, GPS, targa, login, storico, grafici.
```
