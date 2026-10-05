# PreventivoCheck.web — DESIGN.md

> Fase 6a. Derivato da logo/asset Fase 5 (neutri + semaforo). Mobile-first 360px. Pronto per OpenCode React + Tailwind e n8n.

## Palette (hex)

| Ruolo | Hex | Note |
| --- | --- | --- |
| Primary (bottone/CTA) | #111827 | quasi-nero, deriva da logo |
| Primary pressed | #030712 | |
| Accent link | #2563EB | solo link/info, mai per verdetto |
| Neutro scuro / testo | #111827 | |
| Neutro medio | #6B7280 | sub-testi, step |
| Neutro chiaro bordi | #E5E7EB | |
| Sfondo pagina | #FFFFFF | |
| Sfondo muted | #F9FAFB | riepilogo/box |
| Success | #16A34A | 🟢 bordo cerchio #14532D |
| Warning | #EAB308 | 🟡 bordo #854D0E |
| Error | #DC2626 | 🔴 bordo #7F1D1D, anche errori form/rete |
| Focus ring | #2563EB | 2px su input/bottoni |

## Tipografia

- Font: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif (zero dipendenze, italiano).
- Scala: display 28/34 bold (verdetto) / h1 22/28 bold (domande) / h2 17/24 semibold (sezioni) / body 16/24 regular / caption 13/18 regular (step, sub).

## Spacing e radius

- Spacing: 4 / 8 / 12 / 16 / 24 / 32. Margini laterali pagina 16, sezioni 24.
- Radius: card 12px, bottoni 10px, input 10px, pill/step 999px. Ombre morbide solo su card Report: 0 1px 2px rgba(17,24,39,.08).

## Componenti (con stati)

| Componente | Stati |
| --- | --- |
| Bottone primario (Verifica / Copia / Avanti) | default #111827 testo bianco; hover #1F2937; pressed #030712; disabled #9CA3AF su #E5E7EB; loading spinner + label "Analisi..." |
| Bottone secondario (Nuova verifica) | default bordo #E5E7EB testo #111827; hover #F9FAFB; pressed #E5E7EB |
| Link Indietro | default #6B7280; hover #111827 |
| Input € | vuoto placeholder "0,00"; focus bordo #2563EB + ring; compilato bordo #111827; errore bordo #DC2626 + msg |
| Lista selezione (regioni/auto/interventi) | riga 56px, default bianca; pressed #F9FAFB; selezionata bordo #111827 + check; focus ring |
| Card Report | bianca, radius 12, ombra morbida; varianti bordo-top 4px success/warning/error |
| Badge semaforo pill | 🟢 #16A34A / 🟡 #EAB308 / 🔴 #DC2626 su pill chiara con testo scuro |
| Header | 56px, logo sx 18 bold, step dx caption; stesso padding 16 ovunque |
| Box WhatsApp | #F9FAFB, bordo #E5E7EB, testo 14 monospace-ish, bottone Copia sotto |

## Cosa NON definito

- Animazioni complesse, carousel, layout asimmetrici, custom non standard: fuori scope.
