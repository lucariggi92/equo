# import/

Carica nel database i dati della raccolta prezzi (task M4).

| File | Cosa è | Su git |
|---|---|---|
| `importa_dati.py` | lo script di import | sì |
| `elenco-fonti.xlsx` | le fonti con il rating di affidabilità | no |
| `ultimo-import.sql`, `report-import.txt` | SQL generato e report dell'ultimo import | no |

Il file con i dati raccolti (`dati-disponibili.xlsx`) sta fuori dal repository, in `_v2/LISTINI PREZZI/`.

## Uso

Dalla cartella `database-mysql` (serve Python con `openpyxl`):

```powershell
python import/importa_dati.py "..\_v2\LISTINI PREZZI\dati-disponibili.xlsx" --prova   # controlla e annulla
python import/importa_dati.py "..\_v2\LISTINI PREZZI\dati-disponibili.xlsx"           # carica
```

Il report elenca le righe accettate e quelle scartate, con il motivo.

## Regole applicate

- Ogni riga deve avere una fonte presente nel foglio Fonti, l'URL o il documento e la data.
- Fonte **basso**: la riga non entra mai.
- **Prezzi**: basta un campione da fonte **elevato**; altrimenti servono almeno **2 campioni medio** da **negozi di gruppi diversi** (colonna "Gruppo societario" del foglio Fonti: tre siti dello stesso gruppo valgono uno). Si scartano quelli oltre ±50% dalla mediana e si usa la mediana dei restanti. Il netto si calcola togliendo l'IVA della fonte.
- **Fatture** (taratura del ricarico): solo da fonte **elevato** e solo se il codice ha già un prezzo corrente.
- Tutto in una transazione: se qualcosa va storto nel database, non si salva niente.
- Rieseguibile: un prezzo uguale non crea righe nuove; un prezzo diverso chiude il vecchio (`valido_al`) e apre il nuovo.
