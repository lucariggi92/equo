# Schema del database `equo`

Le 18 tabelle create da `schema/01_schema.sql` e i loro collegamenti. GitHub disegna il diagramma da solo; la versione in immagine è in [`../docs/architettura/schema-database-equo.png`](../docs/architettura/schema-database-equo.png).
Nomi delle tabelle in inglese, nomi delle colonne in italiano (revisione PM del 2026-11-12).

```mermaid
erDiagram
  brands ||--o{ car_models : "ha"
  car_models ||--o{ variants : "ha"
  variants ||--o{ intervention_components : "richiede"
  interventions ||--o{ intervention_components : "composto da"
  components ||--o{ intervention_components : "pezzo"
  variants ||--o{ part_qualities : "monta"
  components ||--o{ part_qualities : "pezzo"
  spare_parts ||--o{ part_qualities : "codice"
  spare_parts ||--o{ part_prices : "prezzo nel tempo"
  part_prices ||--o{ price_samples : "mediana di"
  sources ||--o{ price_samples : "prova"
  variants ||--o{ labor_hours : "tempo"
  interventions ||--o{ labor_hours : "tempo"
  sources |o--o{ labor_hours : "prova"
  regions ||--o{ labor_rates : "tariffa"
  sources |o--o{ labor_rates : "prova"
  sources ||--o{ calibrations : "fattura"
  spare_parts ||--o{ calibrations : "pezzo"
  regions ||--o{ calibrations : "dove"
  variants |o--o{ quote_requests : "auto"
  interventions |o--o{ quote_requests : "scelto"
  regions ||--o{ quote_requests : "dove"

  brands { smallint id PK
    varchar nome UK }
  car_models { smallint id PK
    smallint marca_id FK
    varchar nome
    varchar segmento
    bool info_completed }
  variants { smallint id PK
    smallint modello_id FK
    varchar etichetta
    enum alimentazione
    enum distribuzione
    varchar olio_specifica
    smallint anno_produzione_inizio
    smallint anno_produzione_fine }
  interventions { tinyint id PK
    varchar nome UK
    enum tipo "pacchetto o singolo"
    bool visibile_nel_form }
  components { smallint id PK
    varchar descrizione UK
    enum categoria
    enum unita "pezzo set kit litro" }
  intervention_components { smallint variante_id PK,FK
    tinyint intervento_id PK,FK
    smallint componente_id PK,FK
    decimal quantita }
  spare_parts { int id PK
    varchar produttore UK
    varchar codice UK
    varchar descrizione }
  part_qualities { smallint variante_id PK,FK
    smallint componente_id PK,FK
    enum qualita PK
    int ricambio_id FK }
  part_prices { int id PK
    int ricambio_id FK
    decimal prezzo_netto
    enum tipo_fonte "ufficiale o media"
    tinyint n_campioni
    date valido_dal
    date valido_al }
  price_samples { int id PK
    int prezzo_id FK
    int fonte_id FK
    decimal prezzo_fonte
    bool iva_inclusa
    decimal prezzo_netto
    bool scartato }
  sources { int id PK
    enum tipo
    varchar riferimento
    enum affidabilita "basso medio elevato"
    varchar url
    date data_rilevazione }
  labor_hours { smallint variante_id PK,FK
    tinyint intervento_id PK,FK
    decimal ore
    enum origine
    int fonte_id FK }
  regions { tinyint id PK
    varchar nome UK
    enum area }
  labor_rates { int id PK
    tinyint regione_id FK
    enum tipo_officina
    decimal tariffa_oraria_netta
    enum origine
    date valido_dal
    date valido_al
    int fonte_id FK }
  markups { int id PK
    enum tipo_officina
    enum tipo_fonte
    decimal fattore
    enum origine
    date valido_dal
    date valido_al }
  calibrations { int id PK
    int fonte_id FK
    int ricambio_id FK
    tinyint regione_id FK
    enum tipo_officina
    decimal prezzo_fattura_netto
    decimal prezzo_riferimento_netto
    decimal rapporto "calcolato" }
  quote_requests { int id PK
    datetime creata_il
    smallint variante_id FK
    smallint anno_immatricolazione
    tinyint intervento_id FK
    varchar altro_intervento
    enum qualita
    tinyint regione_id FK
    enum tipo_officina
    decimal importo_preventivo
    decimal stima_calcolata
    enum esito }
  settings { varchar chiave PK
    varchar valore }
```

## Corrispondenza con i nomi vecchi

| Prima | Ora | | Prima | Ora |
|---|---|---|---|---|
| impostazioni | `settings` | | ricambi | `spare_parts` |
| regioni | `regions` | | applicazioni | `part_qualities` |
| fonti | `sources` | | prezzi_ricambi | `part_prices` |
| marche | `brands` | | prezzi_campioni | `price_samples` |
| modelli | `car_models` | | ore_stimate | `labor_hours` |
| varianti | `variants` | | tariffe_manodopera | `labor_rates` |
| interventi | `interventions` | | ricarichi | `markups` |
| componenti | `components` | | tarature | `calibrations` |
| distinta_base | `intervention_components` | | interventi_esclusi | *(eliminata)* |
| — | `quote_requests` *(nuova)* | | | |

`car_models` e non `models`: in Laravel una classe `Model` andrebbe in conflitto con la classe base di Eloquent.

## Tabelle senza collegamenti diretti

- **`markups`**: si legge con la coppia (`tipo_officina`, `tipo_fonte`). `tipo_fonte` arriva dal prezzo del ricambio, `tipo_officina` dalla scelta dell'utente. Il valore si ricava dalla mediana di `calibrations`.
- **`settings`**: parametri globali (IVA, soglie del verdetto, ore minime).

## Come si legge una stima

1. L'utente sceglie **variante**, **intervento**, **qualità**, **regione** e **tipo officina**.
2. `intervention_components` elenca i componenti e le quantità per quella variante e quell'intervento.
3. `part_qualities` trova il codice in `spare_parts` per ogni componente nella qualità scelta.
4. `part_prices` dà il prezzo corrente del codice (`valido_al` vuoto), e il suo `tipo_fonte`.
5. `markups` dà il fattore per (`tipo_officina`, `tipo_fonte`).
6. `labor_hours` × `labor_rates` (regione + tipo officina) dà la manodopera.
7. Alla somma si applica `settings.iva_pct`.
8. La richiesta e il verdetto si salvano, anonimi, in `quote_requests`.
