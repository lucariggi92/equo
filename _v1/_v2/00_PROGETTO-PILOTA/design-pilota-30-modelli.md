# EQUO — Pilota 30 modelli del parco circolante

**Data:** 2026-10-27
**Stato:** bozza per approvazione
**Sostituisce:** il calcolo benchmark nel nodo Code di n8n (archiviato in `_v1/`)

---

## 1. Intento e perché

EQUO verifica se il preventivo di un meccanico è congruo. Oggi l'app confronta il prezzo con numeri inventati dentro un workflow n8n (20 tariffe, 8 auto, 5 interventi, nessuna fonte). Il pilota sostituisce quei numeri con un **database MySQL di prezzi reali rilevati oggi** e un **backend PHP** che applica il modello della presentazione `_v2/costruzione database.pptx`:

```
ricambi   = Σ (prezzo_catalogo × quantità) × (1 + ricarico)
manodopera = ore_stimate × tariffa_oraria
stima      = (ricambi + manodopera) × 1,22
```

Obiettivo del pilota: verificare che il modello regga su **30 modelli diffusi nel parco circolante italiano**, che i dati siano raccoglibili a mano in tempi accettabili e che l'app sappia dire su cosa si basa la stima.

---

## 2. Obiettivi

1. Stima **scomponibile e verificabile**: ricambi (codice, quantità, qualità), manodopera (ore × tariffa), ricarico, IVA.
2. Copertura: **30 modelli**, 2-3 varianti reali ciascuno, **5 interventi**, **3 qualità** selezionabili.
3. Ogni prezzo ha **fonte, data e numero di campioni**; il report mostra la data dei dati.
4. Frontend: due nuove scelte (qualità ricambio, tipo officina) e report con spaccato.
5. Stack: React invariato, **PHP 8.2+ (PDO)**, **MySQL/MariaDB**.
6. Ambiente: locale (XAMPP/MAMP) per il pilota; deploy su hosting condiviso dopo.

---

## 3. Non-obiettivi (per questo pilota)

- Targa, VIN, visura PRA, API esterne.
- Export PDF, GPS, login/account, storico verifiche, dati utente.
- Aggiornamento automatico, scraping, API a pagamento (TecDoc/Autodata).
- Pannello admin: si usano Excel/CSV + phpMyAdmin.
- Tutti i modelli in commercio e tutti gli allestimenti.
- Distinzione OEM/aftermarket oltre i tre livelli previsti.
- Refresh periodico dei listini: il pilota è una fotografia datata.

---

## 4. Anti-obiettivi (fallimento anche se "funziona")

- Numeri inventati o non riconducibili a una fonte.
- Copertura dichiarata ma non reale (modelli presenti a metà).
- Un'app che mostra una precisione che i dati non hanno.
- Una raccolta così lenta da non essere finita.
- Un database che va rifatto appena si aggiunge un modello.

---

## 5. Vincoli

| Vincolo | Conseguenza |
|---|---|
| Nessuna fonte precostituita | Raccolta manuale, una persona |
| Tempo limitato | Esecuzione a ondate, con copertura dichiarata |
| Budget | Hosting 30-80 €/anno; nessuna API a pagamento |
| Privacy | Nessun dato utente: solo dati di riferimento |
| Dati statici | Ogni prezzo è datato; niente refresh nel pilota |
| Competenze | Stack semplice, PHP procedurale + PDO, nessun framework |

---

## 6. Approcci considerati

| Approccio | Pro | Contro | Esito |
|---|---|---|---|
| **Supabase** (Postgres gestito + API + pannello) | Pannello dati pronto, RPC SQL, deploy rapido | Lock-in, RLS, progetto in pausa se inattivo | Scartato per scelta del committente |
| **Node + Postgres** | Controllo totale, stessa forma di risposta di n8n | Due servizi, più codice, niente pannello | Scartato |
| **Dataset statico (Excel → JSON)** | Zero backend e costi, deploy statico | Nessun DB/query, aggiornare = rideployare | Scartato: non è un database |
| **PHP + MySQL** (scelto) | Hosting condiviso, phpMyAdmin, no lock-in, stesso dominio | Più codice, hosting da pagare | **Scelto** |

**Perché PHP + MySQL:** i dati sono piccoli e relazionali, l'app è in sola lettura, non servono WebSocket né job in background. PHP 8.2 + OPcache + MySQL con indici risponde in pochi millisecondi. phpMyAdmin copre la manutenzione iniziale e il modello resta portabile.

---

## 7. Decisioni prese (con il committente)

1. Universo: **auto che circolano**, non il listino del nuovo.
2. **30 modelli** più diffusi, 2-3 varianti reali ciascuno.
3. **5 interventi**: Tagliando completo, Freni, Cinghia distribuzione, Frizione, Batteria.
4. L'utente **sceglie** qualità ricambio (originale / equivalente / economico) e tipo officina (concessionaria / generica / catena).
5. Chiave dei ricambi: **codice ricambio**, non la descrizione.
6. Prezzo: **listino ufficiale se esiste (1 valore)**, altrimenti **media di 3 campioni** di mercato.
7. Inserimento **manuale** (Excel + import), nessuna automazione, nessuna targa.
8. Il pilota è una **fotografia datata**: nessun aggiornamento periodico previsto.
9. Backend **PHP**, database **MySQL**, frontend **React** invariato.

---

## 8. Modello dati (MySQL)

### 8.1 Tabelle

| Tabella | Campi principali | Note |
|---|---|---|
| `marche` | `id`, `nome` | ~30 marche |
| `modelli` | `id`, `marca_id`, `nome`, `segmento` | es. Panda, Golf |
| `varianti` | `id`, `modello_id`, `motore`, `cilindrata`, `alimentazione`, `anno_da`, `anno_a` | la chiave reale per i ricambi |
| `interventi` | `id`, `nome`, `descrizione` | 5 righe |
| `componenti` | `id`, `descrizione`, `categoria` | slot globale: disco freno ant., filtro olio… |
| `distinta_base` | `variante_id`, `intervento_id`, `componente_id`, `quantita` | cosa serve, indipendente dalla qualità |
| `ricambi` | `id`, `codice`, `produttore`, `descrizione` | anagrafica codici, `codice` UNIQUE |
| `applicazioni` | `variante_id`, `componente_id`, `qualita`, `ricambio_id` | quale codice concreto per quello slot, quella variante, quella qualità |
| `prezzi_ricambi` | `id`, `ricambio_id`, `prezzo_netto`, `prezzo_lordo_fonte`, `iva_fonte`, `tipo_fonte`, `n_campioni`, `prezzo_min`, `prezzo_max`, `data_rilevazione`, `valido_dal`, `valido_al`, `fonte_id` | storico per codice |
| `fonti` | `id`, `tipo`, `riferimento`, `url`, `file`, `data` | prova del prezzo |
| `regioni` | `id`, `nome` | 20 regioni |
| `tariffe_manodopera` | `regione_id`, `tipo_officina`, `tariffa_oraria_netta`, `data_rilevazione` | 20 × 3 |
| `ricarichi` | `regione_id`, `tipo_officina`, `fattore`, `data_rilevazione` | 20 × 3 |
| `ore_stimate` | `variante_id`, `intervento_id`, `ore` | tempario |
| `impostazioni` | `chiave`, `valore` | IVA, soglie |

### 8.2 Perché `componenti` + `applicazioni` + `ricambi`

Un disco freno è **uno slot** (`componenti`); lo stesso slot su varianti diverse può avere **codici diversi** (un 1.2 e un 1.6 montano dischi di diametro diverso). Quindi:

- `distinta_base` dice *cosa serve* per una variante e un intervento, senza nominare la qualità;
- `applicazioni` dice *quale codice* riempie quello slot per quella variante e quella qualità;
- `ricambi` è l'anagrafica dei codici (un codice può comparire in più applicazioni: stesso disco su Golf e Octavia);
- `prezzi_ricambi` è agganciato al codice, così un prezzo si raccoglie **una volta** e si riusa su tutte le auto compatibili.

Questo risolve due errori: non si duplica la distinta per le tre qualità e non si assume che due varianti dello stesso modello usino lo stesso pezzo.

### 8.3 Indici minimi

```sql
CREATE INDEX idx_distinta_variante ON distinta_base (variante_id, intervento_id);
CREATE INDEX idx_applicazioni      ON applicazioni (variante_id, componente_id, qualita);
CREATE UNIQUE INDEX uq_codice      ON ricambi (codice);
CREATE INDEX idx_prezzi_ricambio   ON prezzi_ricambi (ricambio_id, valido_al);
CREATE INDEX idx_tariffe           ON tariffe_manodopera (regione_id, tipo_officina);
CREATE INDEX idx_ore               ON ore_stimate (variante_id, intervento_id);
```

### 8.4 Regola del prezzo corrente

Per ogni `ricambio` si usa la riga con `valido_al IS NULL` (o `>= oggi`) e `valido_dal` più recente. Il pilota avrà una sola fotografia; la struttura è già pronta per il refresh.

---

## 9. Calcolo e verdetto

### 9.1 Formula e convenzione sull'IVA

**Tutti i valori nel database sono IVA esclusa.** Se la fonte è un prezzo al pubblico (IVA inclusa, come quasi tutti gli e-commerce), in import si scarta l'IVA: `netto = lordo / 1,22`. Si conservano comunque `prezzo_lordo_fonte` e `iva_fonte` per tracciabilità. L'IVA si riapplica **una sola volta**, alla fine. Lo stesso vale per tariffe orarie e ricarichi: si registrano netti.

Senza questa regola si sommano due volte IVA e margine, con una sovrastima del 40-50% che sposta tutti i verdetti su "equo".

```
ricambi    = Σ (prezzo_netto_ricambio × quantità)    -- per ogni riga di distinta
ricarico   = ricambi × fattore_ricarico(regione, officina)
manodopera = ore_stimate × tariffa_oraria_netta(regione, officina)
imponibile = ricambi + ricarico + manodopera
stima      = imponibile × (1 + IVA)                   -- IVA 22%
```

Il ricarico si applica **solo ai ricambi**: la tariffa oraria è già il prezzo di vendita della manodopera.

### 9.2 Confronto col preventivo

- `scostamento = prezzo_richiesto − stima`
- `pct = scostamento / stima`
- Soglie (invariate rispetto al MVP):
  - `pct ≤ +10%` → **equo**
  - `+10% < pct ≤ +25%` → **sopra la media**
  - `pct > +25%` → **anomalo**

Un preventivo molto **sotto** la stima (es. −50%) resta "equo", ma il report mostra un avviso "molto sotto la stima: verifica cosa è incluso". Nessun quarto verdetto nel pilota.

Assunzione: il prezzo richiesto è **IVA inclusa** (come nella quasi totalità dei preventivi al privato). Se in futuro servirà, si aggiunge un flag.

### 9.3 Dati mancanti

Se manca il prezzo di uno o più componenti per la qualità scelta, l'API **non inventa**: restituisce `completo: false`, l'elenco dei componenti mancanti e una stima parziale. Il report lo dichiara ("stima su 4 ricambi su 6") e il verdetto diventa "dati insufficienti" se la copertura è sotto il 100%.

### 9.4 "Non lo so" (fuori dal pilota)

Deciso: nel pilota **qualità e tipo officina sono scelte obbligatorie**, perché un intervallo tra i due estremi sarebbe troppo largo per essere leggibile. Il meccanismo resta progettato nel database: il backend sa calcolare la stima per ogni combinazione, quindi "Non lo so" si aggiungerà dopo come funzione che mostra l'intervallo (economico/generica ↔ originale/concessionaria) invece di un verdetto secco.

---

## 10. API PHP

Base: `/api` sullo stesso dominio del frontend.

| Metodo | Endpoint | Scopo |
|---|---|---|
| GET | `/api/opzioni.php` | regioni, marche, modelli, varianti, interventi, qualità, tipi officina |
| GET | `/api/varianti.php?modello_id=` | varianti di un modello |
| POST | `/api/verifica.php` | calcolo stima + verdetto |
| GET | `/api/salute.php` | stato DB e data ultimo dato (per diagnostica) |

### 10.1 Request

```json
{
  "regione_id": 1,
  "variante_id": 42,
  "intervento_id": 2,
  "qualita": "equivalente",
  "tipo_officina": "generica",
  "prezzo": 280
}
```

`qualita` e `tipo_officina` ammettono solo i valori previsti (`originale`, `equivalente`, `economico`, `concessionaria`, `generica`, `catena`).

### 10.2 Response

```json
{
  "completo": true,
  "manodopera": { "ore": 2.0, "tariffa": 38.0, "totale": 76.0 },
  "ricambi": [
    { "codice": "1K0615301A", "descrizione": "Disco freno ant.", "quantita": 2, "prezzo_unitario": 62.5, "totale": 125.0 }
  ],
  "ricarico": { "fattore": 0.18, "totale": 22.5 },
  "imponibile": 223.5,
  "iva": 49.17,
  "stima": 272.67,
  "scostamento": 7.33,
  "verdetto": "equo",
  "meta": {
    "data_dati": "2026-10-27",
    "fonti": 12,
    "assunzioni": ["preventivo IVA inclusa", "qualità equivalente", "officina generica"]
  }
}
```

`prezzo_unitario` e `prezzo_netto` sono **netti di IVA**; l'IVA compare una sola volta nel campo `iva`.

### 10.3 Sicurezza

- PDO con **query preparate**, mai concatenazione.
- Whitelist per `qualita`, `tipo_officina`, `regione_id`, `intervento_id`; `prezzo` numerico > 0 e < 100000.
- Nessun dato utente salvato; nessun log con IP.
- CORS ristretto all'origine del frontend (o stesso dominio, quindi inutile).
- Credenziali in `config.php` fuori dal repo (`.gitignore`), con `config.example.php`.

---

## 11. Flusso UI aggiornato

```
0 · Benvenuto
1 · Regione
2 · Auto        → marca → modello → variante (etichettata con motore e anni:
                  "1.2 benzina 2012-2018")
3 · Intervento  (5)
4 · Qualità ricambio (3, scelta obbligatoria)
5 · Tipo officina (3, scelta obbligatoria)
6 · Prezzo      (tastierino)
7 · Loading     (con annullamento reale, no race)
8 · Report      (spaccato + verdetto + data + WhatsApp)
```

- La **variante è obbligatoria**: i ricambi cambiano con generazione e motore. L'etichetta mostra motore e anni, così l'utente riconosce la sua auto senza conoscere codici interni. Chi non conosce la versione la individua sul libretto (campo J.2): nel pilota **nessun fallback** a livello di modello, perché applicherebbe ricambi sbagliati.
- Il selettore auto diventa **marca → modello → variante**: 30 modelli × 2-3 varianti richiedono gerarchia e ricerca.
- Il report mostra: ricambi riga per riga, manodopera, ricarico, IVA, stima, scostamento, data dei dati, numero fonti.
- La promessa "60 secondi" resta valida: 6 scelte + importo.

---

## 12. Raccolta dati

### 12.1 Struttura cartelle

```
_v2/00_PROGETTO-PILOTA/
├── LISTINI PREZZI/
│   ├── Listini casa madre/     # PDF/screenshot listini OEM
│   ├── Listini mercato/        # screenshot + URL e-commerce
│   └── copertura.xlsx          # tracker = fonte di verità
├── design-pilota-30-modelli.md
└── (codice) api/  db/  frontend/
```

### 12.2 Excel `copertura.xlsx` — fogli

1. **Modelli**: marca, modello, segmento, incluso Sì/No, note.
2. **Varianti**: modello, motore, cilindrata, alimentazione, anni, codice interno.
3. **Distinta**: variante, intervento, componente, quantità.
4. **Applicazioni**: variante, componente, qualità, codice, produttore, descrizione.
5. **Prezzi**: codice, qualità, prezzo_lordo_fonte, iva_fonte, prezzo_netto, tipo_fonte (ufficiale/media), n_campioni, min, max, data, file_fonte.
6. **Manodopera**: regione, tipo officina, tariffa_netta, data, fonte.
7. **Ricarichi**: area, tipo officina, fattore, data, fonte.
8. **Ore**: variante, intervento, ore, fonte.
9. **Copertura**: pivot per modello × intervento × qualità (verde/giallo/rosso).
10. **Log**: data, cosa, chi, note.

> `prezzo_netto` è l'unico valore che entra nei calcoli. `prezzo_lordo_fonte` e `iva_fonte` servono solo a ricostruire la fonte.

### 12.3 Metodo

- **Listino ufficiale** (OEM): un valore, `tipo_fonte = ufficiale`, screenshot/PDF archiviato.
- **Mercato**: 3 campioni per codice+qualità da ricambisti/e-commerce nazionali, `tipo_fonte = media`, si registra **mediana**, min, max, data e URL.
- **Scarto**: se un campione è fuori dal 50% dalla mediana, si annota e si sostituisce; se non si trova un terzo campione, si dichiara `n_campioni = 2`.
- **Naming file**: `MARCA_MODELLO_ANNO_CODICE_QUALITA_FONTE.ext`.
- **Regola d'oro**: nessun prezzo senza fonte. Meglio un buco dichiarato che un numero inventato.

**Ore, tariffe e ricarichi** — non esistono listini pubblici affidabili per tutti. Deciso: nel pilota si usano **assunzioni dichiarate** (`tipo_fonte = assunzione`) con range documentati (Nord/Centro/Sud per le tariffe, 15-25% per i ricarichi) e si **calibrano** con 3 officine reali sugli 10 modelli dell'Onda A. Le interviste estese arrivano dopo, se il pilota regge. Ogni valore conserva data e nota.

**OEM introvabile** — su modelli di oltre ~10 anni il listino casa può non essere pubblico o il pezzo è fuori produzione. In quel caso il prezzo `originale` resta assente e la stima va in `completo: false`: non si sostituisce con un prezzo di categoria.

### 12.4 Volume atteso

| Voce | Stima |
|---|---|
| Modelli | 30 |
| Varianti (2,5 per modello) | ~75 |
| Distinte (varianti × 5 interventi) | ~375 |
| Righe distinta (componenti per intervento) | ~950 |
| Componenti unici per variante | ~450 |
| Codici (× 3 qualità) | ~1.350 |
| Prezzi (3 campioni per codice) | ~4.000 rilevazioni |

È un lavoro di settimane, non di giorni. Per questo l'esecuzione è a ondate.

### 12.5 Contenuto dei 5 interventi (approvato)

| Intervento | Componenti | Quantità | Note |
|---|---|---|---|
| **Tagliando completo** | Olio motore · filtro olio · filtro aria · filtro abitacolo | 1 ciascuno | + filtro gasolio se diesel · + candele se benzina |
| **Freni** | Disco freno ant. · pastiglie ant. | 2 · 1 set | **Solo anteriore**: posteriori fuori copertura |
| **Cinghia distribuzione** | Kit cinghia · pompa acqua | 1 · 1 | Volano escluso |
| **Frizione** | Kit frizione (disco · spingidisco · cuscinetto) | 1 kit | Volano escluso |
| **Batteria** | Batteria | 1 | Smaltimento incluso nella manodopera |

**Manodopera:** minimo **0,5 h** per intervento, anche se il calcolo effettivo è inferiore. Il minimo si applica a ogni riga di ore stimate.

**Fuori copertura, dichiarato:** freni posteriori, volano, liquido freni come voce separata, olio di freni, kit di rifacimento pastiglie posteriori.

---

## 13. Esecuzione a ondate (non è il piano esecutivo)

- **Onda A — 10 modelli** (i più diffusi): completa su 5 interventi e 3 qualità. Serve a validare schema, Excel, import, API e UI.
- **Onda B — altri 20 modelli**: stessa procedura, con tempi misurati sull'Onda A.
- Ogni onda chiude con un report di copertura reale.

---

## 14. Ambiente e deploy

### 14.1 Locale

L'app del pilota nasce in `_v2/00_PROGETTO-PILOTA/app/`; `_v1` resta intatta e consultabile. Non si patcha il frontend v1 in place.

- XAMPP o MAMP: Apache + PHP 8.2+ + MySQL/MariaDB.
- DB `equo`, utente dedicato, charset `utf8mb4`.
- Import Excel → CSV → phpMyAdmin.
- Frontend: `npm run dev`, proxy Vite `/api` → `http://localhost/equo/api`.
- Backend: Apache (document root del progetto) oppure `php -S localhost:8000 -t public`.

### 14.2 Produzione

- Stesso dominio: build React in `/`, PHP in `/api`.
- `.htaccess` per bloccare accessi a `config.php`, `db/`, file `.xlsx`.
- HTTPS obbligatorio.
- Backup del DB prima di ogni import.

---

## 15. Criteri di accettazione

1. I 30 modelli sono presenti con almeno una variante; la copertura reale è mostrata, non dichiarata.
2. Per ogni modello completo, i 5 interventi restituiscono una stima con spaccato.
3. Il 100% dei prezzi usati ha `fonte` e `data_rilevazione`.
4. L'API risponde in < 200 ms su DB locale con ~5.000 righe di prezzo.
5. Il report mostra data dati, numero fonti e assunzioni.
6. Nessun prezzo inventato: i buchi sono dichiarati (`completo: false`).
7. `npm run build` e `npm run lint` puliti; nessun errore console nel flusso completo.
8. Il flusso funziona da 360 px senza scroll orizzontale.

---

## 16. Decisioni confermate

1. **Contenuto dei 5 interventi** — vedi §12.5 (freni solo anteriori, minimo 0,5 h, no volano).
2. **Ore, tariffe, ricarichi** — assunzioni dichiarate + calibrazione su 3 officine sull'Onda A (§12.3).
3. **"Non lo so"** — fuori dal pilota, scelta obbligatoria (§9.4).
4. **IVA** — preventivo IVA inclusa; sotto −25% resta "equo" con avviso (§9.2).
5. **Hosting** — tutto in locale per il pilota.
6. **Copertura** — Onda A di 10 modelli congelata, altri 20 rivedibili (Appendice A).

**Chiamate tecniche fatte dal builder** (verificarle leggendo):

- Ricarico per **area** (4 aree × 3 tipi = 12 righe), non per regione.
- **Variante obbligatoria** con etichetta motore/anni.
- App **nuova in `_v2`**, v1 intatta.
- Prezzi sempre **netti** di IVA, storico con `valido_dal/valido_al`.
- Schema a tre livelli `componenti + applicazioni + ricambi`.

**Resta aperto:** la lista definitiva dei 10 modelli dell'Onda A (proposta in Appendice A).

---

## 17. Decisioni lasciate al builder

- Struttura del router PHP e nomi dei file.
- Scelta XAMPP vs MAMP.
- Dettagli di stile del report (fermo restando DESIGN-DARK).
- Strategia di import CSV (una tabella per volta, transazione).
- Eventuale cache delle opzioni lato client.

---

## 18. Rischi

| Rischio | Impatto | Mitigazione |
|---|---|---|
| Raccolta più lenta del previsto | Pilota incompleto | Onda A da 10 modelli, misura tempi |
| Codici introvabili per alcuni ricambi | Buchi nei dati | Fallback descrittivo + `completo: false` |
| Dati incoerenti tra fonti | Stime errate | Mediana + min/max + scarto annotato |
| Hosting con PHP vecchio | API non funziona | Verifica `php -v` prima del deploy |
| Termini d'uso degli e-commerce | Rischio legale | Solo uso interno/verifica, screenshot, no ripubblicazione |
| Modello dati troppo rigido | Rifacimento | `varianti` + `componenti` + storico prezzi |

---

## Appendice A — Lista dei 30 modelli

Da validare con le vendite cumulative (il parco per modello non è pubblico).

### A.1 — Onda A (10 modelli, congelata)

| # | Modello |
|---|---|
| 1 | Fiat Panda |
| 2 | Fiat Punto |
| 3 | Lancia Ypsilon |
| 4 | Fiat 500 |
| 5 | Volkswagen Golf |
| 6 | Renault Clio |
| 7 | Toyota Yaris |
| 8 | Ford Fiesta |
| 9 | Peugeot 208 |
| 10 | Dacia Sandero |

### A.2 — Resto dei 30 (rivedibile prima dell'Onda B)

| # | Modello | # | Modello |
|---|---|---|---|
| 1 | Fiat Panda | 16 | Citroën C3 |
| 2 | Fiat Punto | 17 | Toyota Yaris |
| 3 | Fiat 500 | 18 | Toyota Corolla |
| 4 | Lancia Ypsilon | 19 | Dacia Sandero |
| 5 | Ford Fiesta | 20 | Dacia Duster |
| 6 | Ford Focus | 21 | Opel Corsa |
| 7 | Volkswagen Golf | 22 | Opel Astra |
| 8 | Volkswagen Polo | 23 | Nissan Qashqai |
| 9 | Volkswagen Tiguan | 24 | Audi A3 |
| 10 | Renault Clio | 25 | Mercedes Classe A |
| 11 | Renault Captur | 26 | BMW Serie 1 |
| 12 | Renault Mégane | 27 | Alfa Romeo Giulietta |
| 13 | Peugeot 208 | 28 | Jeep Renegade |
| 14 | Peugeot 2008 | 29 | Fiat 500X |
| 15 | Smart Fortwo | 30 | Hyundai Tucson |

Riserve: Kia Sportage, Seat Ibiza, Škoda Octavia, Mercedes Classe C, Audi A4.

---

## Appendice B — Perché non la targa

- La visura PRA è a pagamento; le API commerciali hanno costi e minimi.
- I cataloghi ricambi (TecDoc/Autodata) sono abbonamenti.
- La targa è un dato personale: rompe la promessa "zero targa, zero dati".
- Anche con la targa esatta servono comunque codici e prezzi: non risolve la raccolta.
- Il database è già keyed su `variante`, quindi un risolutore targa/VIN si potrà innestare senza rifare lo schema.
