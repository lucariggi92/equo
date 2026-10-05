# Prompt Stitch — applica palette dark EQUO (copia-incolla)

> Sorgenti: `12-Definizione logo e palette/1.jpg` + `2.png`. Dettagli in `DESIGN-DARK.md` qui accanto.

```txt
Applica questo design system dark ai 6 wireframe esistenti, senza cambiare layout, struttura o testi. Viewport 360px, header 56px, footer sticky 64px, margini 16px.

TOKENS (usa solo questi hex esatti):
- Sfondo pagina e header/footer: #0B0F19
- Card e input: #1A365D, bordi #2D4A7A
- Testo primario #FFFFFF, secondario #A0AEC0
- CTA primaria (Verifica / Copia / Avanti): fondo #38A169, testo #0B0F19 bold; hover #2F855A, pressed #276749, disabled #2D3748 con testo #718096
- Focus ring e link: #38A169 2px
- Verdetto success #38A169 (pill outline + icona, non bottone pieno), warning #ECC94B, error #FC8181
- Font system-ui, radius card 12px bottoni/input 10px

LOGO: wordmark EQUO bianco #FFFFFF in header, maiuscolo come da riferimento.

VINCOLI: solo questi hex, niente gradienti, niente altri blu, niente immagini vere. Applica un componente alla volta se fatichi: prima sfondi/testi, poi CTA, poi input, poi badge semaforo.
```

## Manuale in Stitch (se il prompt sbaglia)

1. Theme / Design System → Colors → inserisci a mano: `0B0F19`, `1A365D`, `2D4A7A`, `FFFFFF`, `A0AEC0`, `38A169`, `2F855A`, `276749`, `ECC94B`, `FC8181`.
2. Verifica su 1 schermata (Regione): sfondo `#0B0F19`, righe su `#1A365D`, CTA Mint con testo scuro.
3. Controlla contrasto CTA + semaforo: mai bottone Mint pieno accanto a pill success Mint senza icona/bordo.
