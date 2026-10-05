# Fase 5 — Asset Creation (parallela alla Fase 4)

> Scopo: generare con AI gli **asset visivi** del progetto (logo, hero image,
> icone, illustrazioni, immagini prodotto) coerenti col brand emerso in
> scoping/MVP0. Si puo' fare in parallelo alla Fase 4 (wireframe): i wireframe
> definiscono *dove* vanno gli asset, questa fase li *produce*.

**Input attesi:** brand/tono da `Agent.md`, mappa schermate (Fase 3), wireframe
in corso (Fase 4).
**Output:** file immagine degli asset + `05-asset.md` compilato come registro.
**Tool default:** tool GenAI immagini (Gemini, Claude, o altri). Agnostico.

---

## Direzione di brand

Prima di generare, fissa la direzione (serve coerenza tra tutti gli asset).

- Mood (3-4 aggettivi):
  > (compila, es. "pulito, caldo, affidabile")
- Palette di riferimento (anche solo indicazioni, gli hex esatti in Fase 6a):
  > (compila)
- Cosa evitare (stili fuori tono):
  > (compila)

Prompt di supporto (se vuoi aiuto a fissare la direzione):
```txt
Ruolo: Sei un brand designer che fissa una direzione visiva.
Contesto: Prodotto: [MVP0]. Target: [chi]. Tono desiderato: [se hai idee].
Obiettivo: Proponi il mood in 3-4 aggettivi, una palette indicativa (con hex di
           partenza) e 3 stili da evitare perche' fuori tono.
Vincoli: aggettivi concreti (non "moderno"); palette coerente col target; hex
         reali; 1 riga di perche' il mood si adatta al target.
Formato: Mood / Palette (hex) / Da evitare.
```

## Prompt di generazione asset

Un prompt per tipo di asset. Riusa mood e palette per tenerli coerenti.

Prompt (logo / immagine):
```txt
Genera [tipo asset: logo / hero image / icona / illustrazione] per un'app di
[dominio].
Mood: [3-4 aggettivi coerenti col brand].
Uso: [dove andra': es. header della Home, sfondo hero della landing].
Vincoli visivi: [palette indicativa, stile piatto/realistico, con/senza testo].
Formato: [es. PNG trasparente, orizzontale 16:9, quadrato 1:1].
Coerenza: stesso stile visivo degli altri asset del progetto.
```

## Registro asset

Traccia ogni asset generato: dove va, con quale tool, e se e' coerente col brand.

| Asset | Uso previsto | Schermata target | Tool | Coerente col brand? | Stato |
| --- | --- | --- | --- | :---: | --- |
| logo | | | | | |
| hero | | | | | |
| | | | | | |
| | | | | | |

Convenzione di naming/posizione file (es. `/assets/logo.png`):
> (compila)

## Cosa NON ho generato (di proposito)

Evita di produrre asset per feature no-goal o schermate non nel flusso MVP0.

- Asset per feature no-goal: niente.
- Varianti stilistiche fuori dal mood scelto: scartate.
- Altri:
  -

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: annotati mood/brand e la posizione della cartella asset.
- [ ] `workspace.md`: accodato blocco Fase 5 con i prompt di generazione e gli
      asset tenuti/scartati.

## Verifica rapida

- [ ] Tutti gli asset condividono lo stesso mood/stile?
- [ ] Ogni asset ha un uso previsto e una schermata target?
- [ ] Nessun asset per feature no-goal?
- [ ] I file sono nominati e collocati in modo che la Fase 6 li ritrovi?
