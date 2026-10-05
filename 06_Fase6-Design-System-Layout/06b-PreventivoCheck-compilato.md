# Fase 6b — Layout compilato — PreventivoCheck.web — 2026-09-10

> Template `06b-layout.md` intoccato. Redesign vero da wireframe Fase 4 + DESIGN.md + asset Fase 5.

Schermata redesign iniziale: 6 Report (la più ricca: semaforo + spaccato + WhatsApp + 2 CTA).

## Direzione estetica

Aggettivi operativi: spaziosa (whitespace 24), piana (niente gradienti), decisa (1 protagonista per schermata).
Ispirazione: app bancarie costo-trasparenza (card bianca + cifra bold) e wizard assicurativi (1 domanda, lista 56px, footer sticky).
Prendo: card verdetto con bordo-top semantico + cifra delta in pill bold; liste full-width con check a destra.

## Varianti Report

| Variante | Descrizione |
| --- | --- |
| A | Card verdetto centrata + semaforo 64px + delta pill + spaccato 2 righe + box WhatsApp + footer sticky Copia |
| B | Hero semaforo full-width colorato |
| C | Spaccato a grafico barre orizzontali |

Selezione: A.
1. Criterio chiarezza: A mostra verdetto + delta + azione in 1 viewport senza scroll orizzontale.
2. Criterio fattibilità: A usa solo card/bottoni/input DESIGN.md, B/C richiedono hero/grafici custom fuori scope.
Scartata più allettante: C (barre) perché décor senza decisione extra e costi Tailwind custom.

## Estrai stile -> DESIGN.md

- [x] DESIGN.md già aggiornato (card Report bordo-top 4px semantico, pill delta bold, footer sticky 16px).

## Layout finali (dall'alto in basso, 360px, margini 16)

1. Regione: header 56 (logo + 1/4) / H1 domanda / lista 20x56px + check / footer sticky Avanti disabled fino a scelta.
2. Auto: header + 2/4 / H1 / search + lista 10x56px / footer Avanti/Indietro.
3. Intervento: header + 3/4 / H1 / 5 card 72px con sub / footer Avanti/Indietro.
4. Prezzo: header + 4/4 / H1 / riepilogo muted 3 righe / input € 28px + tastierino / footer Verifica primario full-width.
5. Loading: header senza step / spinner 48 centrato + "Analisi dei listini in corso..." / errore con Riprova.
6. Report: header + Nuova / card verdetto (semaforo SVG 64 + H1 + pill delta) / spaccato Ricambi/Manodopera / box WhatsApp / footer Copia primario + Nuova secondario.

## Audit cross-screen

- [x] Header 56 identico, [x] bottoni stesso stile, [x] margini 16, [x] naming IT, [x] niente bottom nav (wizard, non serve).

## Asset integrati

| Asset | Schermata | Zona |
| --- | --- | --- |
| logo.svg | tutte | header sx |
| semaforo-verde/giallo/rosso.svg 64px | 6 Report | card verdetto |
| spinner.svg 48px | 5 Loading | centro |

## Cosa NON messo

Niente micro-interazioni spinte, carousel, asimmetrie, custom non standard.
