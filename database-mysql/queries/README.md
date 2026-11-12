# queries/

Query di lavoro sul database, una per file:

| File previsto | Task | Cosa fa |
|---|---|---|
| `calcolo_stima.sql` | M6 | stima di un intervento: ricambi + ricarico + manodopera + IVA |
| `controlli_copertura.sql` | M7 | prezzi mancanti, varianti senza composizione, righe scadute |
| `taratura_ricarico.sql` | M8 | mediana delle `calibrations` verso `markups` |

Quando parte Laravel, la logica di `calcolo_stima.sql` passa nel Service del backend; questa query resta come riferimento per i test.
