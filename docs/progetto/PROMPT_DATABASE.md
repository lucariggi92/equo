# PROMPT per altro agente — Progettazione database stimatore prezzi officina (MySQL)

## Contesto e obiettivo

Progetta e produci lo **schema di un database MySQL** per un sistema di **stima prezzi di intervento auto** in officina. Il sistema deve calcolare un preventivo a partire da 5 input forniti dall'utente, senza chiedere all'utente di inserire dati tecnici che il sistema deve conoscere già (quantità, composizione degli interventi, prezzi, tariffe).

## Formula di calcolo (vincolo invariante)

```
PREZZO STIMATO = RICAMBI + MANODOPERA + IVA

RICAMBI    = Σ righe: (prezzo catalogo UNITARIO × quantità × (1 + fattore di ricarico))
MANODOPERA = Σ righe: (ore stimate × tariffa oraria)
IVA        = imponibile × 22%   (percentuale PARAMETRIZZATA, non hardcodata)
```

## Input dell'utente (esattamente 5, nessun altro)

1. Area geografica
2. Modello Auto
3. Tipo Officina
4. Tipo di intervento
5. Qualità ricambio (Originale / Equivalente / Economico)

## Decisioni progettuali già prese (da rispettare)

1. **Prezzo di catalogo UNITARIO**: in `CATALOGO` si salva sempre il prezzo per 1 pezzo. La quantità entra solo nel calcolo.
2. **La quantità NON viene mai inserita dall'utente**: è master data in tabella, letta dal sistema. Esempio: l'utente digita "pastiglie freno anteriori", il sistema legge 4 e lo riempie da solo.
3. **La quantità varia per modello** (es. 4,5 litri di olio su una Golf, 3,5 su una Punto).
4. **Gli interventi sono COMPOSTI (distinta)**: l'utente digita "tagliando" e il sistema espande automaticamente tutte le righe figlie (filtro olio, filtro aria, olio, manodopera) senza che l'utente le inserisca una a una.
5. **I componenti di un intervento cambiano in base a TUTTE le variabili**: modello auto, qualità ricambio, area geografica, tipo officina. Quindi la composizione va indicizzata su tutte e 5 le dimensioni (insieme al tipo intervento). Esempio: su un Golf con ricambi originali in una concessionaria del Nord serve un olio specifico VW; su un Golf con ricambi economici in un'indipendente del Sud può servire un olio generico. Cambiano **i pezzi stessi**, non solo i litri.
6. **Fattore di ricarico dell'officina**: dipende da Tipo Officina + Area Geografica.
7. **Tariffa oraria**: dipende da Tipo Officina + Area Geografica.
8. **Ore stimate**: dipendono da Tipo di intervento + Modello Auto.
9. **Prezzo ricambio**: si legge dal catalogo con chiave Modello Auto + Tipo intervento + Qualità ricambio.
10. Il preventivo è composto da **righe** (le Σ della formula): righe di tipo RICAMBIO e righe di tipo MANODOPERA.

## Tabelle da produrre (DDL MySQL)

Produci lo schema SQL completo (CREATE TABLE) per almeno queste entità:

### Tabelle di lookup / anagrafiche
- `AREA_GEOGRAFICA` — id, descrizione
- `TIPO_OFFICINA` — id, descrizione (es. concessionaria, indipendente, catena)
- `QUALITA_RICAMBIO` — id, descrizione (Originale / Equivalente / Economico)
- `TIPO_INTERVENTO` — id, descrizione (es. tagliando, pastiglie freno anteriori)
- `MODELLO_AUTO` — id, marca, modello, versione/eventuali attributi tecnici
- `OFFICINA` — id, nome, tipo_officina_id, area_id (FK)

### Tabelle dati / logica di prezzo
- `CATALOGO` — chiave (modello_id, tipo_intervento_id, qualita_id); `prezzo_catalogo` DECIMAL unitario
- `RICARICO_OFFICINA` — chiave (tipo_officina_id, area_id); `fattore_ricarico`
- `ORE_STIMATE` — chiave (modello_id, tipo_intervento_id); `ore`
- `TARIFFA_ORARIA` — chiave (tipo_officina_id, area_id); `tariffa_oraria`
- `INTERVENTO_COMPONENTI` (la distinta) — chiave (tipo_intervento_id, modello_id, qualita_id, area_id, tipo_officina_id, riferimento); `tipo_riga` ENUM('RICAMBIO','MANODOPERA'); `pezzi_necessari` (o ore, per la manodopera)
  - Da questa tabella il sistema espande automaticamente tutte le righe del preventivo quando l'utente sceglie l'intervento

### Tabelle transazionali
- `PREVENTIVO` — id, data, riferimenti, iva_pct, imponibile, totale
- `PREVENTIVO_RIGA` — id, preventivo_id, tipo_riga ENUM('RICAMBIO','MANODOPERA'), descrizione, riferimento, quantita, prezzo_unitario, importo

## Specifiche tecniche MySQL

- Engine **InnoDB**, charset **utf8mb4**
- Vincoli di **chiave primaria** su tutte le tabelle; **foreign key** esplicite con nomi coerenti (`fk_...`)
- Denaro in **DECIMAL(10,2)**; ore/quantità in **DECIMAL(6,2)**
- Nomi tabelle maiuscoli, colonne minuscole snake_case
- Aggiungi indici dove serve (lookup frequenti)
- Dove sensato, commenti sulle tabelle (COMMENT '...')

## Flusso di calcolo da descrivere (in SQL o pseudocodice)

Dato i 5 input, mostrare come:
1. Si espande la distinta dell'intervento → righe (componenti/quantità/ore)
2. Per ogni riga RICAMBIO si legge prezzo unitario da CATALOGO e fattore da RICARICO_OFFICINA → importo = prezzo × quantità × (1 + ricarico)
3. Per ogni riga MANODOPERA si legge ORE_STIMATE e TARIFFA_ORARIA → importo = ore × tariffa
4. Somma → imponibile → + IVA → totale stimato

## Domande aperte (segnalale, non risolverle arbitrariamente)

- Serve **storicità/validità** dei listini (prezzi e tariffe con date dal/al)? Se sì, aggiungi le colonne e chiedi conferma.
- Ci sono **sconti** o **minimi di manodopera**? Non implementare se non richiesto, ma segnala.
- Cliente e Veicolo (targa) sono necessari al preventivo o il preventivo è anonimo? Segnala, non inventare.

## Output atteso

1. **DDL MySQL completa** (script eseguibile, con CREATE DATABASE opzionale)
2. **Esempio di dati di test** (INSERT) per un caso reale: tagliando su Golf
3. **Query di calcolo** del preventivo di esempio con i 5 input
4. Breve **schema riassuntivo** delle relazioni (elenco FK)
