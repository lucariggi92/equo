# Fase 1 — Scoping

> Scopo: partire da un'idea grezza e arrivare a una comprensione solida del
> problema, del target e del contesto. Ciclo: **ricerca → evidenziazione →
> raffinazione → sintesi**. A fine fase generi i due artefatti vivi:
> `Agent.md` (v1) e apri `workspace.md`.

**Input attesi:** un'idea iniziale (anche vaga).
**Output:** `Agent.md` v1, `workspace.md` aperto, scheda-scoping compilata sotto.
**Tool default:** Gemini o Claude (ricerca + sintesi). Alternativa: qualsiasi LLM.

---

## 1. Ricerca

Esplora problema, target, contesto e alternative esistenti. Non decidere ancora:
raccogli.

Prompt (da incollare):
```txt
Ruolo: Sei un ricercatore di prodotto che mi aiuta a capire un problema
       prima di progettare una soluzione.
Contesto: Ho questa idea grezza: [descrivi l'idea in 2-3 righe].
Obiettivo: Aiutami a capire (1) chi ha davvero questo problema, (2) in quale
           contesto lo vive, (3) come lo risolve oggi senza il mio prodotto,
           (4) quali alternative esistono gia'.
Vincoli: non inventare numeri o statistiche; se non sai, scrivi "da verificare";
         distingui fatti da ipotesi.
Formato: 4 sezioni (Chi / Contesto / Soluzioni attuali / Alternative), elenchi puntati.
```

Output grezzo (incolla o linka in `workspace.md`):
> (compila)

## 2. Evidenziazione

Rileggi l'output e marca cosa tenere. Separa segnale da rumore.

| Insight tenuto | Perche' rilevante | Da verificare? |
| --- | --- | :---: |
| | | |
| | | |
| | | |

## 3. Raffinazione

Torna sull'AI sui punti deboli o marcati "da verificare".

Prompt (da incollare):
```txt
Ruolo: Continua come ricercatore di prodotto critico.
Contesto: Dalla ricerca precedente ho tenuto questi punti: [incolla gli insight
          tenuti]. Questi li ho marcati come dubbi: [incolla i "da verificare"].
Obiettivo: Sfida i punti dubbi: quali sono le assunzioni piu' rischiose?
           Cosa dovrei verificare prima di costruire?
Vincoli: massimo 5 assunzioni rischiose; per ciascuna un modo semplice per
         verificarla; niente numeri inventati.
Formato: tabella (Assunzione | Rischio se falsa | Come verifico).
```

Assunzioni rischiose emerse:
> (compila)

## 4. Sintesi

Consolida in una scheda e genera gli artefatti vivi.

### Scheda-scoping

Prompt di supporto (se vuoi aiuto a compilarla dagli output di ricerca):
```txt
Ruolo: Sei un product researcher che sintetizza in una scheda.
Contesto: Ecco gli insight tenuti e le assunzioni rischiose: [incolla
          evidenziazione + raffinazione].
Obiettivo: Compila una bozza di scheda-scoping con: problema (1 frase), target
           concreto, contesto d'uso, valore atteso, ipotesi principali, rischi
           da verificare.
Vincoli: problema in una frase; target concreto (non "tutti"); separa ipotesi da
         fatti; marca "da verificare" dove serve; niente numeri inventati.
Formato: i 6 campi elencati, uno per riga.
```

- Problema (1 frase):
  > (compila)
- Target (chi, concreto):
  > (compila)
- Contesto d'uso:
  > (compila)
- Valore atteso (cosa migliora per l'utente):
  > (compila)
- Ipotesi principali:
  > (compila)
- Rischi / assunzioni da verificare:
  > (compila)

### Genera Agent.md v1

Prompt (da incollare):
```txt
Ruolo: Sei un assistant che scrive istruzioni di progetto sintetiche.
Contesto: Ecco la mia scheda-scoping: [incolla la scheda qui sopra].
Obiettivo: Scrivi un file Agent.md che riassuma il progetto per un'altra AI:
           cosa costruisco, per chi, il problema, i vincoli noti, i no-goal
           evidenti. Corto e corrente.
Vincoli: massimo 1 pagina; niente parti superate; distingui vincoli da ipotesi;
         niente em dash.
Formato: markdown con sezioni brevi.
```

## Aggiorna gli artefatti vivi

- [ ] `Agent.md` creato (v1) dai risultati della sintesi.
- [ ] `workspace.md` aperto: accodato il blocco Fase 1 con i prompt usati e le
      decisioni.

## Verifica rapida

- [ ] Il problema e' scritto in una frase, con un target concreto (non "tutti")?
- [ ] Ho separato fatti da ipotesi e marcato i "da verificare"?
- [ ] `Agent.md` v1 esiste e si legge in 30 secondi?
- [ ] `workspace.md` contiene i prompt usati per intero?
