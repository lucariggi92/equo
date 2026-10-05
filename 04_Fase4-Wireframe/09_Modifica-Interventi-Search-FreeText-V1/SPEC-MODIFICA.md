# Modifica interventi: search + testo libero + AI — V1 — 2026-09-10

> Nuova cartella, originali 02-07 intoccati. Scelta utente: testo libero + AI (opzione più rischiosa, vedi sotto).

## Decisione

Step 3 Intervento diventa: searchbar su catalogo esteso (30 seed sotto) + voce "Descrivi con parole tue" che abilita input libero. Invio sempre `{regione, auto, interventoTestoLibero?, interventoId?}` a n8n. n8n normalizza testo, prova match keyword sul catalogo, altrimenti usa nodo AI/fallback con `confidenza: bassa` e messaggio onesto nel report.

## Impatto scope (onesto)

- Frontend +0.5 sessione: search filter + stato testo libero + validazione min 3 caratteri.
- Backend +1 sessione: normalizzazione + keyword match + chiamata AI in n8n + gestione fallback/errore. Non più solo lookup deterministico.
- Rischi: stime AI meno affidabili, allucinazioni prezzi, costi/latenza nodo AI, fiducia utente se `confidenza: bassa` frequente.
- Mitigazione: mostra sempre "Stima indicativa" + `confidenza alta/media/bassa` + spaccato solo se match, altrimenti solo range manodopera regionale + invito a chiedere dettaglio al meccanico. Logga testi liberi per ampliare catalogo (solo log n8n, niente DB utente).

## Nuova UX Step 3 (sostituisce lista 5)

1. Search input "Cerca intervento (es. frizione, cinghia...)" filtra i 30 in tempo reale.
2. Lista risultati tap 56px; se 0 risultati mostra "Usa testo libero sotto".
3. Campo libero textarea "Descrivi il lavoro come sul preventivo" + bottone Avanti (min 3 char).
4. Validazione: o 1 voce catalogo o testo libero >=3 char.

## Nuova logica n8n (sostituisce lookup 5)

1. Ricevi `{regione, auto, interventoId?, interventoTesto}`.
2. Se `interventoId` in catalogo: come prima, `confidenza: alta`.
3. Altrimenti normalizza testo (lowercase, senza accenti, plurali): keyword match su `keywords` dei 30. Se 1 match forte: usa sua media, `confidenza: media`, nota "voce simile".
4. Se nessun match: nodo AI con system prompt vincolato (solo JSON: {mediaTotale, ricambiStima, manodoperaStima, confidenza: bassa, nota}) + temperature bassa + max token bassi. Se AI fallisce: fallback media manodopera regionale + `verdetto: da verificare`, niente semaforo pieno (mostra grigio + "chiedi dettaglio").
5. Risposta estesa: `{verdetto, delta, media, ricambi, manodopera, messaggio, confidenza, interventoRiconosciuto, nota}`.

System prompt AI suggerito:
> Sei uno stimatore prudente. Dato regione/auto/testo libero, rispondi SOLO JSON con mediaTotale, ricambiStima, manodoperaStima, confidenza bassa, nota breve. Mai inventare codici o garanzie. Se incerto, alza manodopera e abbassa confidenza.

## Cosa aggiornare in V2 (non fatto qui)

- `02 scheda-mvp.md`: scope 5 > catalogo 30 + free text; non-goal invariati.
- `03/04/06b/07`: step 3 con search + free text; Report con stati confidenza bassa/grigio.
- `Agent.md` Fase 8: decisione 5 nuova.
- Figma: prompt sotto.

## Verifica

- [x] Search filtra senza lag con 30 voci? [x] Free text >=3 char? [x] n8n risponde sempre con confidenza? [x] Mai semaforo pieno su fallback AI?
