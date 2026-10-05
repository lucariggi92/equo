# Fase 6a — Design System (DESIGN.md)

> Scopo: definire il **design system PRIMA** di comporre i layout. Sistema prima
> di schermate: decisioni prima di pixel. Il design system vive in un file
> **`DESIGN.md`** del progetto, cosi' fa da ponte diretto verso il build (un tool
> di AI coding come Lovable puo' leggerlo direttamente).

**Input attesi:** brand/mood (Fase 5), asset generati, wireframe (Fase 4).
**Output:** un file `DESIGN.md` del progetto compilato con questo schema.
**Tool default:** editor design system di Stitch (genera `DESIGN.md` nativo), o
lo scrivi a mano con il prompt sotto.

Regole d'oro:
- **Hex sempre**, mai nomi colore ("blu" -> shade a caso; `#2563EB` -> preciso).
- **Deriva i colori dal logo/asset** della Fase 5, non a caso.
- Definisci i componenti **uno alla volta** se il tool fatica.

---

## Prompt per generare DESIGN.md

```txt
Crea un design system per un'app mobile di [dominio].
Brand: [3-4 aggettivi di mood].

PALETTE (usa hex, deriva dai colori del logo/asset):
- Primary: #XXXXXX
- Accent: #XXXXXX
- Neutri: scala di grigi + bianco
- Semantic: success #XXXXXX, error #XXXXXX, warning #XXXXXX

TIPOGRAFIA: [font], scala: display / h1 / h2 / body / caption
SPACING: scala 4 / 8 / 12 / 16 / 24 / 32
RADIUS: [es. 8px card, 6px bottoni]

COMPONENTI (con tutti gli stati):
- Bottone primario / secondario / ghost: default, hover, pressed, disabled, loading
- Input field: vuoto, focus, compilato, errore
- Card [dominio]
- Bottom navigation
- Badge / pill
```

## DESIGN.md — schema da compilare

### Palette (hex)

Prompt di supporto (se vuoi aiuto a estrarre la palette dal logo):
```txt
Ruolo: Sei un designer che estrae una palette da un logo.
Contesto: Allego il logo/asset principale: [immagine]. Mood: [aggettivi].
Obiettivo: Estrai la palette in hex: primary, accent, neutri (scala) e colori
           semantici (success/error/warning) coerenti.
Vincoli: solo hex; deriva primary/accent dai colori reali del logo; neutri in
         scala; semantici leggibili su sfondo chiaro.
Formato: tabella Ruolo | Hex.
```

| Ruolo | Hex | Note |
| --- | --- | --- |
| Primary | | |
| Accent | | |
| Neutro scuro | | |
| Neutro chiaro | | |
| Bianco/superficie | | |
| Success | | |
| Error | | |
| Warning | | |

### Tipografia

- Font: (compila)
- Scala: display / h1 / h2 / body / caption -> (dimensioni)

### Spacing e radius

- Spacing: 4 / 8 / 12 / 16 / 24 / 32
- Radius: card (compila) / bottoni (compila)

### Componenti (con stati)

| Componente | Stati definiti |
| --- | --- |
| Bottone primario | default, hover, pressed, disabled, loading |
| Bottone secondario / ghost | |
| Input field | vuoto, focus, compilato, errore |
| Card | |
| Bottom navigation | |
| Badge / pill | |

## Cosa NON ho definito (di proposito)

- Micro-interazioni / animazioni complesse: fuori scope MVP0.
- Componenti custom non standard: no (devono restare ricostruibili nel build).
- Altri:
  -

## Aggiorna gli artefatti vivi

- [ ] `Agent.md`: annotato che esiste `DESIGN.md` e dove si trova.
- [ ] `workspace.md`: accodato blocco Fase 6a con il prompt DESIGN.md usato.

## Verifica rapida

- [ ] Tutti i colori sono in hex (nessun nome colore)?
- [ ] I colori derivano dal logo/asset, non a caso?
- [ ] Ogni componente ha i suoi stati elencati?
- [ ] Il file `DESIGN.md` e' salvato nel progetto e pronto da passare al layout?
