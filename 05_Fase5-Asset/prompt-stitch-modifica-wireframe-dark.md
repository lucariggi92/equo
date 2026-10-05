# Prompt Stitch — Fase 5: applica logo + colore dark al wireframe neutro

> Sorgenti in questa cartella: `assets/logo-equo-dark.svg` (wordmark EQUO bianco),
> `Definizione-logo-palette/1.jpg` (logo su dark), `Definizione-logo-palette/2.png`
> (palette `#0B0F19` `#1A365D` `#38A169`). Mood: diretto, affidabile, essenziale, rassicurante.
> Allega a Stitch: `logo-equo-dark.svg` + `1.jpg` + `2.png`.

```txt
Modifica i 6 wireframe neutri esistenti applicando logo e palette dark, SENZA cambiare
layout, struttura, testi o flusso. Wizard EQUO, mobile 360px, header 56px, footer sticky
64px, margini 16px. Allegati: logo-equo-dark.svg, 1.jpg (riferimento logo), 2.png (palette).

PASSO 1 — LOGO: in tutte le 6 schermate sostituisci il testo segnaposto nell'header con
il logo allegato (wordmark EQUO bianco #FFFFFF, altezza 20-22px, a sinistra). A destra
resta lo Step (1/4, 2/4, 3/4, 4/4, Analisi..., Verdetto Finale). Niente altro cambia
nell'header.

PASSO 2 — PALETTE (usa SOLO questi hex esatti):
- Sfondo pagina, header e footer: #0B0F19
- Card, liste e input: #1A365D con bordi #2D4A7A
- Testo primario #FFFFFF, secondario #A0AEC0
- CTA primaria full-width (Avanti / Verifica Preventivo / Copia messaggio): fondo
  #38A169, testo #0B0F19 bold; stati hover #2F855A, pressed #276749,
  disabled #2D3748 con testo #718096
- Focus ring e link Indietro: #38A169, 2px
- Semaforo Report: success #38A169 (pill con bordo + icona, MAI bottone pieno),
  warning #ECC94B, error #FC8181
- Font system-ui, radius card 12px, bottoni/input 10px, pill 999px

PASSO 3 — PER SCHERMATA (solo colore, stessa struttura):
1. Regione: righe 56px su #1A365D, selezionata bordo #38A169 + check, Avanti disabled
   finché nessuna selezione.
2. Auto: search + lista 10 su #1A365D, box info "Non trovi il modello?" con bordo
   #2D4A7A.
3. Intervento: 5 card 72px su #1A365D, selezionata bordo #38A169, textarea 96px con
   contatore 0/250.
4. Prezzo+Verifica: riepilogo 3 righe muted #A0AEC0, input € grande su #1A365D con
   focus #38A169, tastierino touch, CTA #38A169 full-width.
5. Loading: spinner #38A169 centrato + "Analisi dei listini in corso...", errore con
   bordo #FC8181 + Riprova, secondario Annulla.
6. Report: cerchio semaforo + H1 verdetto #FFFFFF + pill scostamento, 2 righe
   spaccato manodopera/ricambi, box WhatsApp su #1A365D bordo #2D4A7A, CTA Copia
   #38A169 + secondario Nuova verifica con bordo.

VINCOLI: solo gli hex sopra, niente gradienti, niente altri blu, niente foto/icone 3D,
niente micro-animazioni. Se fatichi, applica un componente alla volta: prima
sfondi+testi, poi CTA, poi input, poi badge. Azione principale sempre in evidenza.
```

## Dopo l'export (dentro questa fase)

1. Scarica da Stitch `code.html` + `screen.png` delle 6 schermate colorate.
2. Verifica: header logo bianco su `#0B0F19`? CTA Mint con testo scuro leggibile?
   Semaforo distinguibile dalla CTA (icona+bordo, non solo colore)?
3. Solo quando tutto ok si passa alla Fase 6 (DESIGN dark definitivo).
