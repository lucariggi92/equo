# Prompt Stitch — modifica Step 3 search + free text

```txt
Modifica solo la schermata "3 Intervento" esistente, mantieni DESIGN.md (palette #111827, neutri #E5E7EB/#F9FAFB, font system-ui, radius card 12 / input 10, header 56px, margini 16px) e stile variante A.

TRASFORMA la lista statica 5 card in:
1. Searchbar 48px in alto "Cerca intervento (es. frizione, cinghia...)" con icona lente, focus ring #2563EB.
2. Lista risultati filtrabile 30 voci, righe 56px full-width con check a destra quando selezionata, scroll verticale.
3. Stato vuoto: testo "Nessun risultato, descrivi sotto" quando filtro = 0.
4. Textarea 96px sotto "Descrivi il lavoro come sul preventivo" + contatore, bordo errore #DC2626 se <3 char.
5. Footer sticky: Avanti primario #111827 abilitato solo se 1 voce selezionata OPPURE testo >=3 char, altrimenti disabled #9CA3AF. Indietro link invariato.

NON cambiare: header, step 3/4, H1, margini, altri componenti, altre schermate. Mobile 360px, italiano, tap grandi.
```
