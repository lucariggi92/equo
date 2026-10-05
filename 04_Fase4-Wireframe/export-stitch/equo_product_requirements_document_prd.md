# EQUO — Product Requirements Document (PRD)

**Versione:** 1.0  
**Stato:** Approvato per sviluppo / Prototipazione  
**Piattaforma:** Mobile Web App (Responsive viewport 360×800px standard)  
**Target:** Utenti automobilisti italiani  
**Nome Prodotto:** EQUO (ex PreventivoCheck.web)

---

## 1. Executive Summary & Visione di Prodotto

### 1.1 Il Problema
La manutenzione automobilistica e i preventivi delle officine meccaniche soffrono di una forte asimmetria informativa. L'automobilista medio non conosce:
- Il costo medio orario della manodopera nella propria area geografica/regione.
- Il costo di listino medio o compatibile dei ricambi per il proprio modello d'auto.
- Se il prezzo totale richiesto dal meccanico sia equo, leggermente alto o fuori scala.
- Come intavolare una conversazione o chiedere chiarimenti senza risultare conflittuale.

### 1.2 La Soluzione: EQUO
**EQUO** è un wizard mobile web a basso attrito (nessun login obbligatorio, nessun caricamento invasivo di documenti/PDF, nessun tracciamento GPS) che consente all'automobilista di convalidare un preventivo di riparazione/tagliando in meno di 60 secondi attraverso 4 semplici passaggi guidati:
1. **Regione geografica** (costo manodopera parametrato localmente).
2. **Modello auto** (volumi e costi ricambi compatibili).
3. **Tipologia di intervento** (tempari orari e materiali standard).
4. **Importo totale preventivato** (cifra richiesta dal meccanico).

L'output è un **Report di Verdetto Immediato** che calcola lo scostamento rispetto al costo equo di mercato e fornisce una bozza di messaggio cortese e professionale pronta da inviare all'officina su WhatsApp o SMS per richiedere lo scorporo o la revisione dei costi.

---

## 2. Obiettivi di Business & Metriche di Successo (KPI)

### Obiettivi Primari
- **Tasso di Completamento del Funnel (Completion Rate):** > 68% dal passaggio 1 al Report finale.
- **Time to Value:** Conclusione del controllo preventivo in meno di 45-60 secondi.
- **Azione Post-Verdetto (Action Rate):** Almeno il 35% degli utenti che ricevono un verdetto "Sopra la Media" cliccano su *"Copia messaggio per il meccanico"*.
- **Retention / Nuova Verifica:** Tasso di utenti che salvano o riutilizzano il servizio per preventivi alternativi.

---

## 3. Ambito di Progetto (Scope)

### In-Scope (MVP)
- Flusso lineare sequenziale 1 → 2 → 3 → 4 → 5 (Loading) → 6 (Report).
- Interfaccia mobile-first ottimizzata per tap target generosi (min. 48–56px).
- Ricerca e filtro interattivo per regioni, modelli auto e interventi d'officina.
- Tastierino numerico dedicato su misura (screen 4) per digitazione rapida importi.
- Generatore dinamico di messaggio conciliante per il meccanico.
- Condivisione/copia rapida negli appunti (Clipboard API).

### Out-of-Scope (Esclusioni esplicite MVP)
- Nessun sistema di autenticazione, registrazione account o login social.
- Nessun upload o OCR di documenti/PDF scansionati.
- Nessuna geolocalizzazione automatica via GPS del dispositivo.
- Nessuna integrazione o lookup targa / numero di telaio (VIN).
- Nessun sistema di recensione officine o comparatore preventivi con marketplace.
- Nessun grafico complesso o dashboard analytics avanzate per l'utente finale.

---

## 4. User Journey & Architettura Schermate

### 4.1 Step 1: Regione (Step 1/4)
- **Scopo:** Determinare la tariffa oraria media regionale applicata dalle officine meccaniche (benchmark CCIAA / Confartigianato / ANIA).
- **Componenti UI:**
  - Header fisso 56px: brand `EQUO` a sinistra, indicatore `Step 1/4` a destra.
  - H1: *"Qual è la tua Regione?"* con sottotitolo esplicativo.
  - Searchbar rapida per filtraggio regioni italiane.
  - Elenco verticale con 20 righe da 56px (`Regione [Nome] >`).
  - Sticky Footer con pulsante `Avanti` (disabilitato finché non viene selezionata una regione).

### 4.2 Step 2: Auto (Step 2/4)
- **Scopo:** Identificare il segmento e la compatibilità componentistica dei ricambi.
- **Componenti UI:**
  - Header fisso 56px (`EQUO` + `Step 2/4`).
  - H1: *"Che auto guidi?"*.
  - Searchbar da 48px con pulsante filtro.
  - Lista scrollabile dei 10 modelli più frequenti in Italia (es. Fiat Panda, VW Golf, Renault Clio, Toyota Yaris, ecc.) con radio button e sottotitolo alimentazione.
  - Box informativo *"Non trovi il modello esatto?"*.
  - Sticky Footer con doppi comandi: `Indietro` e `Avanti` (attivo su selezione).

### 4.3 Step 3: Intervento (Step 3/4)
- **Scopo:** Mappare l'intervento sul tempario orario e la lista parti standard necessarie.
- **Componenti UI:**
  - Header fisso 56px (`EQUO` + `Step 3/4`).
  - H1: *"Che intervento devi fare?"*.
  - Searchbar con focus ring dedicato (`#2563EB`) per ricerca tra 30+ lavorazioni tipiche.
  - Selezione rapida card a 56px con checkmark attivo (es. Tagliando Completo, Pastiglie e Dischi Freno, Cinghia di Distribuzione, Sostituzione Frizione).
  - Textarea 96px per descrizione libera del lavoro come descritto sul preventivo cartaceo, con contatore caratteri (`0 / 250`).
  - Sticky Footer abilitato se presente una selezione attiva o almeno 3 caratteri nel testo libero.

### 4.4 Step 4: Prezzo + Invio Calcolo (Step 4/4)
- **Scopo:** Raccogliere la cifra totale preventivata e mostrare il riassunto pre-calcolo.
- **Componenti UI:**
  - Header fisso 56px (`EQUO` + `Step 4/4`).
  - H1: *"Quanto ti ha chiesto il meccanico?"*.
  - Card di riepilogo a 3 righe compatta: *Regione*, *Veicolo*, *Intervento selezionato*.
  - Campo di input principale valuta in grande scala tipografica (`€ 280,00 EUR`).
  - Tastierino numerico virtuale touch nativo (layout 3x4 con tasto virgola e backspace).
  - Sticky CTA primaria ad alto contrasto: *"Verifica Preventivo →"*.

### 4.5 Step 5: Elaborazione & Loading State
- **Scopo:** Fornire trasparenza algoritmica durante l'elaborazione del tempario e gestire feedback/retry in caso di latenza.
- **Componenti UI:**
  - Header con stato transitorio (`Analisi...`).
  - Indicatore di avanzamento visivo animato al centro viewport con copy descrittivo: *"Analisi dei listini in corso... Confronto costi orari manodopera regionale e prezzi medi ricambi OEM/Aftermarket"*.
  - Barra di progresso con percentuali e benchmark indicizzati (es. banche dati ANIA / Tempari).
  - Box di fallback e gestione errori di connettività con azione `Riprova`.
  - Pulsante secondario inferiore: `Annulla operazione`.

### 4.6 Step 6: Verdetto Finale & Azione (Report)
- **Scopo:** Consegnare il responso con chiarezza, scorporare la stima e abilitare l'azione di negoziazione.
- **Componenti UI:**
  - Header con badge di stato `Verdetto Finale`.
  - Icona semaforo/alert con esito sintetico: *"Preventivo Sopra la Media"* (o *In Linea* / *Conveniente*).
  - Pill quantitativa di scostamento (es. `+65,00 € rispetto alla stima equa`).
  - Tabella di confronto comparativa su 2 blocchi chiari:
    - *Manodopera stimata:* es. 120,00 € (3h x 40€/h in Lombardia) → Ref. OK.
    - *Ricambi e materiali:* es. 95,00 € (listino medio compatibile) → Ref. OK.
  - Box con testo precompilato pronto per WhatsApp/SMS con tono cortese e non conflittuale.
  - Azione primaria Sticky full-width: *"Copia messaggio per il meccanico"* (con trigger di conferma copia).
  - Azione secondaria: *"Nuova verifica"*.

---

## 5. Specifiche Tecniche & Requisiti Non Funzionali

### 5.1 Design System & UI Guidelines
- **Stile:** Wireframe schematico ad alto contrasto e chiarezza visiva (Material / Clean Minimal).
- **Palette cromatica base:**
  - Primario: `#111827` (Nero testuale e CTA primarie)
  - Superficie di sfondo: `#FFFFFF` / `#F9FAFB`
  - Bordi e separatori: `#E5E7EB`
  - Stato attivo / Focus ring: `#2563EB`
  - Badge alert: Neutro / Slate con supporto per varianti di feedback.
- **Tipografia:** Inter / System-UI, forte gerarchia tra H1 display e label data-point.
- **Dimensioni Tap Target:** Minimo 48px per riga, tasti del tastierino numerico 52px+.

### 5.2 Sicurezza & Privacy
- Zero memorizzazione di dati personali (PII).
- L'utente non immette nomi, cognomi o recapiti telefonici.
- I dati inseriti (auto, regione, prezzo) vengono elaborati in sessione effimera o aggregati in forma totalmente anonima per migliorare gli indici medi di mercato.
