# queries/

Query di lavoro sul database, una per file:

| File previsto | Task | Cosa fa |
|---|---|---|
| `calcolo_stima.sql` | M6 ✅ | procedura `calcola_stima(variante, intervento, qualità, regione, officina, preventivo)`: dettaglio riga per riga, stima con intervallo, quota di prezzi verificati, completezza e verdetto |
| `controlli_copertura.sql` | M7 | prezzi mancanti, varianti senza composizione, righe scadute |
| `taratura_ricarico.sql` | M8 | mediana delle `calibrations` verso `markups` |

Quando parte Laravel, la logica di `calcolo_stima.sql` passa nel Service del backend; questa query resta come riferimento per i test.

## Uso di `calcola_stima`

```sql
source queries/calcolo_stima.sql;   -- crea (o ricrea) la procedura
CALL calcola_stima(1, 1, 'equivalente', 4, 'generica', 230.00);
```

Restituisce due tabelle: il dettaglio (una riga per ricambio più la manodopera, con il metodo del prezzo e l'intervallo D5)
e il riepilogo (imponibile, IVA, stima IVA inclusa con intervallo, percentuali di prezzi verificati/indicativi/stimati,
`completo`, componenti mancanti, scostamento ed esito). Se manca anche un solo prezzo, l'esito è `dati_insufficienti`.
