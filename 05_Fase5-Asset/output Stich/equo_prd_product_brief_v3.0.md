# EQUO — Product Requirements Document (PRD) & Product Brief v3.0

**Documento:** Product Requirements Document (PRD) & Design Specification  
**Prodotto:** EQUO  
**Versione:** 3.0 (Aggiornamento globale: Inclusione Schermata Benvenuto/Mission, rimozione badge step, allineamento UI Dark Flat & Flusso Completo)  
**Piattaforma:** Mobile Web App (Responsive Viewport target: 360×800px)  
**Target di Riferimento:** Automobilisti privati in Italia  
**Stato:** Approvato per Prototipazione e Sviluppo Frontend  

---

## 1. Executive Summary & Product Vision

### 1.1 Il Problema
Il settore delle riparazioni e della manutenzione auto in Italia è caratterizzato da una cronica **asimmetria informativa** tra officina meccanica e cliente finale:
- Mancanza di trasparenza sulle tariffe orarie della manodopera, che variano fortemente da regione a regione.
- Incertezza sul costo effettivo dei ricambi (originali OEM vs compatibili di concorrenza).
- Difficoltà psicologica dell'automobilista nell'intavolare una discussione costruttiva o chiedere chiarimenti sul preventivo senza rischiare attriti o conflitti.

### 1.2 La Soluzione: EQUO
**EQUO** è una web application mobile a bassissimo attrito (zero registrazioni, nessun documento PDF da caricare, nessun accesso GPS, nessuna targa o VIN obbligatoria) che permette all'automobilista di **comprendere la missione del servizio e verificare la congruità di un preventivo di manutenzione in meno di 60 secondi** attraverso un flusso fluido e minimale:
0. **Benvenuto & Mission:** Presentazione del valore del servizio, trasparenza e avvio immediato.
1. **Regione geografica:** Benchmark tariffa oraria locale (dati camerali/artigianato).
2. **Modello veicolo:** Stima costo ricambi compatibili e volumi di circolazione.
3. **Tipologia di intervento:** Tempari standard d'officina per lavorazione tipica o note libere.
4. **Importo preventivato:** Cifra in Euro richiesta dal meccanico, digitata tramite tastierino touch.
5. **Feedback di Calcolo (Loading):** Elaborazione trasparente dei benchmark regionali e ricambi.
6. **Report di Verdetto Immediato:** Esito semaforico, scostamento in Euro e **bozza WhatsApp/SMS amichevole e non conflittuale**, pronta da copiare con un tocco.

---

## 2. Obiettivi di Business & KPI di Successo

| Metrica / KPI | Obiettivo (Target) | Definizione e Misurazione |
|---|---|---|
| **Funnel Completion Rate** | **> 70%** | Percentuale di utenti che dalla schermata iniziale completano il flusso e visualizzano il Report finale. |
| **Time to Value (TTV)** | **< 60 secondi** | Tempo medio impiegato dall'utente per completare tutti i passaggi. |
| **Action Rate sul Verdetto** | **> 38%** | Percentuale di utenti con verdetto "Sopra la Media" che cliccano su *"Copia messaggio per il meccanico"*. |
| **Bounce Rate su Schermata 0** | **< 15%** | Chiarezza della proposta di valore, zero friction e avvio immediato senza login. |
| **Retention / Re-check Rate** | **> 25%** | Utenti che effettuano una verifica per un secondo preventivo o salvano il servizio. |

---

## 3. Ambito di Progetto (Scope Definition)

### 3.1 Funzionalità In-Scope (MVP)
- **Flusso sequenziale a 7 schermate coordinate:**
  0. *0. Benvenuto (Mission & Start)*
  1. *1. Regione*
  2. *2. Auto*
  3. *3. Intervento*
  4. *4. Prezzo + Verifica*
  5. *5. Loading (Feedback Calcolo)*
  6. *6. Report (Verdetto + Azione)*
- **Header fisso 56px minimale:** Wordmark EQUO ad alto contrasto a sinistra, eliminazione totale di badge numerati ("Step 1/4", ecc.) per un'esperienza snella e senza ansia da form.
- **Sticky Footer con tap target generosi (min. 48–56px):** Navigazione primaria chiara (*Indietro* e *Avanti/Verifica/Copia*).
- **Tastierino numerico touch nativo (Screen 4):** Layout 3x4 ad alta usabilità senza dipendere dalla tastiera software di sistema.
- **Generatore bozza messaggio di negoziazione:** Testo pre-formattato con tono conciliante, trasparente e professionale.
- **Copy to Clipboard a 1-tap:** Integrazione con Clipboard API nativa per copiare istantaneamente il messaggio negli appunti.

### 3.2 Esclusioni Esplicite (Out-of-Scope per l'MVP)
- **Nessuna autenticazione o registrazione:** Nessun account, social login o salvataggio email.
- **Nessun OCR o caricamento documenti:** Nessun upload di PDF, scansioni o foto scontrini.
- **Nessun GPS / Geolocalizzazione automatica:** Selezione trasparente della regione da lista.
- **Nessun lookup targa / telaio (VIN):** Nessun accesso a banche dati ACI/PRA.
- **Nessun marketplace o affiliazione officine:** EQUO è un comparatore analitico terzo, imparziale e neutrale.
- **Nessuna memorizzazione PII o tracciamento profilante:** Sessione effimera nel client.

---

## 4. Architettura dell'Informazione & Specifiche delle Schermate

### 4.0 Schermata 0: Benvenuto (Mission & Start)
- **Finalità:** Comunicare in modo immediato lo scopo del servizio (equità e trasparenza), rassicurare l'utente sull'anonimato ed evitare frizioni iniziali.
- **Elementi UI:**
  - Header fisso 56px: Logo EQUO pulito.
  - Badge rassicurativo: *"Zero registrazioni • Gratuito • 60 secondi"*.
  - Titolo H1 display: *"Sai se il preventivo del tuo meccanico è equo?"*.
  - Card "LA NOSTRA MISSION": Spiegazione chiara con 3 punti di forza essenziali (tariffe certificate, prezzi medi ricambi, bozza per chiarimenti).
  - Box privacy: Rassicurazione 100% Anonimo (nessun PDF, nessuna targa, nessun GPS).
  - Metriche quantitative: *"60 sec / 20 Regioni / 100% Gratuito"*.
  - Sticky Footer: CTA primaria verde smeraldo (`#38A169`) *"Inizia la Verifica (60 sec)"*.

### 4.1 Schermata 1: Regione
- **Finalità:** Parametrizzare il costo orario medio della manodopera in base al contesto geografico regionale.
- **Elementi UI:**
  - Header fisso 56px con logo EQUO.
  - H1: *"Qual è la tua Regione?"* con sottotitolo esplicativo sui dati camerali.
  - Barra di ricerca con filtro istantaneo in tempo reale.
  - Elenco verticale scrollabile di 20 righe da 56px (`Regione [Nome] >`).
  - Sticky Footer: CTA primaria `Avanti` abilitata su selezione attiva.

### 4.2 Schermata 2: Auto
- **Finalità:** Determinare il segmento del veicolo e il costo medio dei ricambi compatibili e OEM.
- **Elementi UI:**
  - Header fisso 56px con logo EQUO.
  - H1: *"Che auto guidi?"*.
  - Barra di ricerca rapida con filtro.
  - Lista dei modelli più diffusi in Italia (Fiat Panda, VW Golf, Renault Clio, Toyota Yaris, ecc.) con radio button e tag motorizzazione.
  - Box informativo per modelli alternativi.
  - Sticky Footer: `Indietro` e `Avanti` (attivo su selezione).

### 4.3 Schermata 3: Intervento
- **Finalità:** Mappare la lavorazione richiesta sui tempari standard di officina e kit parti.
- **Elementi UI:**
  - Header fisso 56px con logo EQUO.
  - H1: *"Che intervento devi fare?"*.
  - Barra di ricerca con focus ring dedicato.
  - Card di selezione rapida (72px) per interventi comuni (*Tagliando Completo*, *Freni*, *Cinghia Distribuzione*, *Frizione*, *Batteria*).
  - Textarea opzionale di 96px per inserire note libere dal preventivo con contatore caratteri.
  - Sticky Footer: `Indietro` e `Avanti`.

### 4.4 Schermata 4: Prezzo + Verifica
- **Finalità:** Acquisire l'importo totale richiesto dall'officina e mostrare il riassunto pre-calcolo.
- **Elementi UI:**
  - Header fisso 56px con logo EQUO.
  - H1: *"Quanto ti ha chiesto il meccanico?"*.
  - Card riepilogativa a 3 voci: *Regione*, *Veicolo*, *Intervento*.
  - Display importo in grande scala tipografica (`€ 280,00 EUR`).
  - Tastierino numerico virtuale 3x4 touch friendly con tasto virgola e cancellazione rapida.
  - Sticky Footer: CTA primaria prominente *"Verifica Preventivo →"*.

### 4.5 Schermata 5: Feedback Calcolo (Loading State)
- **Finalità:** Offrire trasparenza durante l'elaborazione dei benchmark regionali e gestire la latenza percepita.
- **Elementi UI:**
  - Header fisso con indicatore *"Analisi..."*.
  - Indicatore circolare animato e copy descrittivo: *"Analisi dei listini in corso... Confronto costi orari manodopera regionale e prezzi medi ricambi OEM/Aftermarket"*.
  - Barra di avanzamento percentuale con riferimento alle banche dati tempario.
  - Card di fallback per gestione errori con opzione `Riprova`.
  - Azione secondaria: `Annulla operazione`.

### 4.6 Schermata 6: Report (Verdetto + Azione)
- **Finalità:** Fornire un responso chiaro, scorporare la stima e abilitare l'azione di negoziazione non conflittuale.
- **Elementi UI:**
  - Header fisso con badge `Verdetto Finale`.
  - Semaforo visivo circolare (Verde / Giallo / Rosso) con H1 del verdetto (es. *"Preventivo Sopra la Media"*).
  - Pill con quantificazione dello scostamento netto (es. `+65,00 € rispetto alla stima equa`).
  - Spaccato analitico comparativo su 2 voci:
    - *Manodopera stimata:* es. 120,00 € (3h × 40€/h in Lombardia).
    - *Ricambi e materiali:* es. 95,00 € (listino medio compatibile).
  - Box WhatsApp/SMS con messaggio precompilato cordiale e costruttivo.
  - Sticky CTA primaria full-width: *"Copia messaggio per il meccanico"*.
  - CTA secondaria: *"Nuova verifica"*.

---

## 5. Design System & Specifiche Visive (Equo Dark Flat)

L'intero flusso implementa un'interfaccia dark flat ad alto contrasto concepita per massimizzare la leggibilità su display OLED mobile:

- **Sfondo Schermo, Header e Footer:** `#0B0F19` (Nero profondo tech)
- **Card, Liste, Box e Tastierino:** `#1A365D` (Blu scuro strutturale ad alta densità)
- **Bordi e Separatori:** `#2D4A7A` (Bordo a contrasto calibrato, 1px)
- **Tipografia Primaria:** `#FFFFFF` (Massimo contrasto, font stack Inter / Sans-Serif su tutte le schermate)
- **Tipografia Secondaria / Muted:** `#A0AEC0` (Slate secondario bilanciato)
- **CTA Primaria & Accenti di Selezione:**
  - Sfondo CTA: `#38A169` (Verde smeraldo equità)
  - Testo CTA: `#0B0F19` (Nero bold ad alto contrasto)
  - Stati interazione: Hover `#2F855A`, Active `#276749`, Disabled `#2D3748`
- **Indicatori Semaforo & Feedback:**
  - Conforme / Equo: `#38A169` (Verde smeraldo)
  - Attenzione / Sopra Media: `#ECC94B` (Giallo caldo)
  - Errore / Anomalia: `#FC8181` (Rosso chiaro bilanciato)
- **Raggi di curvatura (Border Radius):**
  - Card contenitore: `12px`
  - Bottoni e campi input: `10px`
  - Badge e pill di stato: `999px` (Pill piena)

---

## 6. Privacy by Design & Requisiti Non Funzionali

1. **Zero PII (Personally Identifiable Information):** Nessun dato personale memorizzato o trasmesso.
2. **Session Storage Effimero:** I parametri inseriti risiedono nel client e si azzerano al termine della sessione o cliccando *"Nuova verifica"*.
3. **Accessibilità & Tap Target:** Tutti i comandi interattivi rispettano le linee guida WCAG (minimo 48×48px per riga e bottoni, 52px+ per il tastierino).
4. **Coerenza Tipografica:** Unico font stack geometrico sans-serif (Inter) applicato uniformemente dalla schermata di Benvenuto al Report finale.
