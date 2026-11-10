# Scheda MVP — PreventivoCheck.web

Compila i campi e usa l'AI con il prompt di sfida per validare i confini.
Mantieni la definizione di MVP a una frase sola.

## Definizione MVP

Una frase che dice cosa costruisco, per chi, e quale nucleo del problema risolve.

MVP:
> PreventivoCheck.web è una web app mobile-first che permette agli automobilisti italiani di verificare in 30 secondi se il preventivo del meccanico è onesto, confrontandolo con listini medi regionali.

Scope da 2 lab:
> Scope congelato per 3 sessioni di build con React + Tailwind (OpenCode) + n8n senza database: wizard a 4 step sequenziali (Regione, Auto da elenco 10 modelli, Intervento da elenco 5 lavori, Prezzo in €) + chiamata POST a webhook n8n con calcolo in-memory da JSON + dashboard report con Semaforo, spaccato costi e testo WhatsApp copiabile.

## Prompt di sfida (da incollare in Gemini)

```txt
Ruolo: Sei un PM scettico che aiuta un founder a non far esplodere l'MVP.
Contesto: [incolla qui la tua definizione di MVP + target + scope: 2 lab di build].
Obiettivo: Elenca 5 feature che sembrerebbero utili ma che allargherebbero
            lo scope oltre 2 lab.
Vincoli: max 5 feature; 1 riga di perche' allargano lo scope;
          non proporre feature gia' dentro l'MVP; non inventare numeri.
Formato: elenco numerato con "Feature" e "Perche' allarga".
```

## Non-goals

Mappa qui l'output del prompt di sfida. Decidi tu per ciascuna feature se e' un non-goal o un post-MVP.

| Feature | Non-goal | Post-MVP | Perche' |
| --- | :---: | :---: | --- |
| 1. Ricerca auto tramite scansione / inserimento Targa |  | X | Richiede integrazione e pagamento di API esterne di visura targhe con gestione errori per targhe non trovate. |
| 2. Generazione e download report in PDF | X |  | Aggiunge librerie di rendering layout lato client/server e gestione file statici da scaricare. |
| 3. Geolocalizzazione GPS automatica per rilevare la Regione | X |  | Introduce permessi browser, casi di diniego e reverse geocoding invece di un semplice menu a tendina. |
| 4. Calcolo differenziato pezzi Originali (OEM) vs Equivalenti (Aftermarket) |  | X | Raddoppia la complessità dei JSON in-memory in n8n e aggiunge step/scelte nel wizard React. |
| 5. Storico verifiche salvato nel browser (LocalStorage) |  | X | Richiede stato persistente, UI per rivedere/cancellare report e sincronizzazione al reload. |

3 non-goals espliciti (feature concrete che non fai affatto):

1. Download report in PDF — il testo copia/incolla per WhatsApp basta.
2. Geolocalizzazione GPS automatica — menu manuale a 20 regioni risolve in 2 secondi.
3. Login / account utente e database — zero persistenza per scelta architetturale (no DB, no LocalStorage).

## User flow essenziale

Il percorso piu' breve dall'apertura al risultato. Una linea, non un albero.

```txt
[Schermata 1] -> [Schermata 2] -> [Schermata 3] -> [Schermata 4]
```

User flow:
> L'utente apre PreventivoCheck.web e seleziona la Regione > sceglie Marca e Modello da elenco preimpostato > sceglie l'Intervento da elenco > inserisce il prezzo chiesto dal meccanico in € e preme "Verifica Preventivo" > vede animazione "Analisi listini in corso..." > ottiene dashboard Report con Semaforo, spaccato Ricambi/Manodopera e testo WhatsApp pronto da copiare.

## Mappa delle schermate

Una riga per schermata con il suo scopo. Se non sai lo scopo, la schermata non serve.

| Schermata | Scopo | Priorita' |
| --- | --- | :---: |
| 1. Step Regione | Contestualizzare tariffe manodopera su base regionale | Alta |
| 2. Step Auto (Marca+Modello) | Identificare costo ricambi indicativo per 10 auto diffuse | Alta |
| 3. Step Intervento | Identificare 1 dei 5 lavori comuni da stimare | Alta |
| 4. Step Prezzo + CTA Verifica | Raccogliere prezzo meccanico e lanciare POST a n8n | Alta |
| 5. Loading "Analisi listini" | Dare feedback durante calcolo backend | Media |
| 6. Dashboard Report (Semaforo + Spaccato + WhatsApp) | Mostrare verdetto 🟢/🟡/🔴, costi stimati e azione negoziazione | Alta |

## 3 schermate chiave

Le 3 schermate che coprono il percorso felice: inizio, azione principale, esito.

| Ruolo | Schermata |
| --- | --- |
| Inizio | Step 1 — Regione (ingresso wizard) |
| Azione principale | Step 4 — Prezzo + "Verifica Preventivo" (fetch POST a n8n) |
| Esito | Dashboard Report — Semaforo + Spaccato costi + Copia messaggio WhatsApp |

## Nota di decisione

Cosa hai accettato, modificato o rifiutato dell'output del prompt di sfida, e perche'.

| Output AI | Accettato / Modificato / Rifiutato | Perche' |
| --- | --- | --- |
| 1. Targa via API | Accettato come Post-MVP | Utile ma solo dopo validazione uso form manuale; evita costi API e gestione errori ora. |
| 2. Download PDF | Accettato come Non-Goal | Complessità inutile: copia/incolla WhatsApp risolve il job senza librerie/file. |
| 3. GPS automatico | Accettato come Non-Goal | Tendina 20 regioni = 2 secondi, zero permessi e zero geocoding. |
| 4. OEM vs Aftermarket | Accettato come Post-MVP | Precisione extra solo se gli utenti la chiedono; ora basta costo medio complessivo. |
| 5. Storico LocalStorage | Accettato come Post-MVP | Solo se aumenta retention; ora report usa-e-getta senza persistenza. |
