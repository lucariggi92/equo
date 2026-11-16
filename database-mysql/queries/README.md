# queries/

Query di lavoro sul database, una per file:

| File previsto | Task | Cosa fa |
|---|---|---|
| `calcolo_stima.sql` | M6 ✅ | procedura `calcola_stima(variante, intervento, qualità, regione, officina, preventivo)`: dettaglio riga per riga, stima con intervallo, quota di prezzi verificati, completezza e verdetto |
| `controlli_copertura.sql` | M7 ✅ | procedura `controlli_copertura(giorni)`: interventi calcolabili per variante e qualità, motivi dei buchi, ricambi senza prezzo, prezzi vecchi, tariffe/ricarichi mancanti, dati tecnici mancanti |
| `taratura.sql` | M8 ✅ | procedura `taratura()`: ore dal tempario, tariffe orarie confrontate con le fonti pubblicate (confermata / da correggere / fonti discordanti / insufficienti), prezzi al cliente pubblicati contro la stima |

`calcolo_stima.sql` crea anche `prepara_stima`, che fa il calcolo e lo lascia nelle tabelle temporanee: la usano `calcola_stima` e `taratura`, così il calcolo esiste in un solo posto.

Quando parte Laravel, la logica di `calcolo_stima.sql` passa nel Service del backend; questa query resta come riferimento per i test.

## Uso di `calcola_stima`

```sql
source queries/calcolo_stima.sql;   -- crea (o ricrea) la procedura
CALL calcola_stima(1, 1, 'equivalente', 4, 'generica', 230.00);
```

Restituisce due tabelle: il dettaglio (una riga per ricambio più la manodopera, con il metodo del prezzo e l'intervallo D5)
e il riepilogo (imponibile, IVA, stima IVA inclusa con intervallo, percentuali di prezzi verificati/indicativi/stimati,
`completo`, componenti mancanti, scostamento ed esito). Se manca anche un solo prezzo, l'esito è `dati_insufficienti`;
lo stesso vale per tagliando e cambio olio quando non è nota la quantità di olio.

## Uso di `controlli_copertura`

```sql
source queries/controlli_copertura.sql;
CALL controlli_copertura(180);   -- 180 = giorni oltre i quali un prezzo è "vecchio"
```

Conta solo gli interventi possibili su quella versione: niente frizione con il cambio automatico, niente cinghia con la catena.
Restituisce sei tabelle: riepilogo per variante e qualità (interventi calcolabili e quota di valore verificata,
indicativa, stimata); interventi non calcolabili con il motivo; ricambi senza prezzo corrente; prezzi vecchi o scaduti;
tariffe e ricarichi mancanti; dati tecnici mancanti nelle varianti. Usa la stessa regola di completezza di `calcola_stima`.

## Uso di `taratura`

```sql
source queries/calcolo_stima.sql;   -- serve prepara_stima
source queries/taratura.sql;
CALL taratura();
```

Senza fatture reali, la taratura usa valori pubblicati (decisione PM del 2026-11-16). Restituisce cinque tabelle:
ore per variante (tempario o assunzione), ore dal tempario con il valore D6 sostituito, ogni fonte di tariffa confrontata
con il valore EQUO della stessa area, l'esito per tipo di officina, e i prezzi al cliente pubblicati contro la stima.
Le regole (fonti minime, scarto, dispersione, anni) sono in `settings`. La procedura non cambia nessun valore: se l'esito
è "da correggere", la correzione si fa nel foglio Tariffe e si reimporta.
