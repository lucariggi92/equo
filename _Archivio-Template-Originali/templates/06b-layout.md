# Fase 6b — Layout (Wireframe -> Design System + asset)

> Scopo: trasformare i wireframe in **layout** di media/alta fedelta' applicando
> il `DESIGN.md` (Fase 6a) e inserendo gli asset (Fase 5).
> Trappola da evitare: "mantieni il layout, cambia solo i colori" produce un
> **wireframe colorato, non un design**. Serve un **redesign vero**: direzione
> estetica + permesso esplicito di ridisegnare.

**Input attesi:** wireframe (Fase 4), `DESIGN.md` (Fase 6a), asset (Fase 5).
**Output:** layout di ogni schermata + questo file compilato.
**Tool default:** Stitch o Claude design.

Ordine critico (evita il drift tra schermate):
1. Redesign vero di **1 sola** schermata (la piu' ricca). Itera fino a piacere.
2. Estrai lo stile riuscito -> aggiorna `DESIGN.md`.
3. Propaga alle altre via multi-select.

---

## 1. Redesign vero (1 schermata)

Il wireframe e' riferimento di **contenuto e flusso**, non un template da colorare.

Prompt di supporto (se vuoi aiuto a scegliere la direzione estetica):
```txt
Ruolo: Sei un art director.
Contesto: Prodotto: [MVP0]. Target: [chi]. Mood di brand (Fase 5): [aggettivi].
Obiettivo: Proponi una direzione estetica per il redesign: 3-4 aggettivi
           operativi e 2 app di riferimento della stessa categoria da cui
           prendere ispirazione.
Vincoli: aggettivi che si traducono in scelte concrete (spacious->whitespace);
         riferimenti reali e pertinenti al target; niente generico.
Formato: Aggettivi / Ispirazione (2 app) / Cosa prendo da ciascuna.
```

Prompt di redesign (da incollare nel tool di design):
```txt
Ridisegna questa schermata trasformandola da wireframe a UI high-fidelity
di produzione. Usa il wireframe solo come riferimento per CONTENUTO e FUNZIONE,
non per lo stile visivo.

DIREZIONE ESTETICA:
[3-4 aggettivi di mood]
Ispirazione: [riferimento concreto a un'app della stessa categoria].

RIDISEGNA LIBERAMENTE:
- [componente chiave]: profondita' (ombre morbide, radius generoso),
  elemento protagonista, gerarchia forte
- Spacing generoso, whitespace, respiro
- Gerarchia visiva reale: cosa salta all'occhio per primo
- Micro-dettagli: badge, stati, iconografia coerente
- Header con carattere, non barra grigia

VINCOLI (gli unici):
- Stessi contenuti e stesso flusso del wireframe
- Palette e tipografia del DESIGN.md
- Mobile-first

NON limitarti a colorare il wireframe: ripensa il layout come farebbe un
product designer.
```

Leve per pilotare l'output:
- Aggettivi di mood cambiano il concreto: spacious=whitespace, dense=stretto,
  warm=morbido, playful=angoli tondi.
- Linguaggio da designer (non "piu' bello"): floating action button, bottom
  sheet, segmented control, hero image, elevation, chips a scorrimento
  orizzontale, prezzo in pill bold.

Schermata scelta per il redesign iniziale:
> (compila, es. "la piu' ricca: Dettaglio")

### Varianti generate

| Variante | Descrizione breve (1 riga) |
| --- | --- |
| A | |
| B | |
| C | |

### Selezione della variante

Scegli con almeno 2 criteri espliciti tra: chiarezza, gerarchia, coerenza col
target, fattibilita' nel build, riduzione dello scope. Non per estetica.

Variante scelta: > (compila)

1. Criterio: … perche' la scelta lo soddisfa meglio: > (compila)
2. Criterio: … perche' la scelta lo soddisfa meglio: > (compila)

Variante scartata piu' allettante e perche' non va:
> (compila)

## 2. Estrai lo stile -> DESIGN.md

- [ ] Ho aggiornato `DESIGN.md` con lo stile riuscito (token, componenti) prima
      di propagare.

## 3. Coerenza cross-screen (multi-select)

Propaga lo stile a tutte le schermate insieme.

```txt
Applica questo design system a tutte le schermate selezionate:
- Primary: #XXXXXX   Accent: #XXXXXX
- Tipografia: [font], scala coerente
- Radius card: [Npx], bottoni: [Npx]
- Bottom nav e header identici su ogni schermata
Mantieni layout e contenuti; cambia solo lo stile visivo.
```

### Audit manuale (il tool a volte cambia comunque elementi)

- [ ] Header identico su ogni schermata?
- [ ] Bottom navigation identica?
- [ ] Bottone primario stesso stile ovunque?
- [ ] Margini laterali uguali?
- [ ] Naming coerente (no misto IT/EN)?

Micro-fix di una discrepanza (una modifica alla volta, mai "sistema tutto"):
```txt
Allinea l'header di questa schermata a quello della Home:
stesso padding, stessa altezza, stesso stile del titolo.
```

## Asset integrati

| Asset | Schermata | Zona del layout | Coerente col brand |
| --- | --- | --- | --- |
| | | | |

## Cosa NON ho messo (di proposito)

- Micro-interazioni e animazioni: niente.
- Carousel / slider: niente.
- Layout asimmetrici complessi: niente.
- Componenti custom non standard: niente.
- Altri:
  -

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: stato aggiornato (Fase 6b), variante/stile scelto annotato.
- [ ] `workspace.md`: accodato blocco Fase 6b con prompt redesign + selezione.

## Verifica rapida

- [ ] E' un redesign vero, non un wireframe ricolorato?
- [ ] Layout coerente con struttura, gerarchia e contenuti del wireframe?
- [ ] Almeno un asset della Fase 5 integrato e coerente col brand?
- [ ] Variante scelta con 2 criteri espliciti?
- [ ] Audit cross-screen passato (header/nav/bottoni/margini identici)?
