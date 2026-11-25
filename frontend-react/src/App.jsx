import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import Header from './components/Header/Header.jsx';
import Avanzamento from './components/Avanzamento/Avanzamento.jsx';
import Welcome from './screens/Welcome/Welcome.jsx';
import Regione from './screens/Regione/Regione.jsx';
import Auto from './screens/Auto/Auto.jsx';
import Anno from './screens/Anno/Anno.jsx';
import Intervento from './screens/Intervento/Intervento.jsx';
import SceltaSingola from './screens/SceltaSingola/SceltaSingola.jsx';
import Prezzo from './screens/Prezzo/Prezzo.jsx';
import Loading from './screens/Loading/Loading.jsx';
import Report from './screens/Report/Report.jsx';
import { opzioni as caricaOpzioni, verifica } from './api/client.js';
import { anniDiProduzione } from './lib/format.js';
import './App.css';

// passi: benvenuto, 8 domande (una per schermata), attesa, report
const BENVENUTO = 0, REGIONE = 1, AUTO = 2, ANNO = 3, INTERVENTO = 4, QUALITA = 5, OFFICINA = 6, PREZZO = 7, IVA = 8,
  ATTESA = 9, REPORT = 10;
const DOMANDE = { [REGIONE]: 'regione', [AUTO]: 'auto', [ANNO]: 'anno', [INTERVENTO]: 'intervento', [QUALITA]: 'ricambi',
  [OFFICINA]: 'officina', [PREZZO]: 'importo', [IVA]: 'IVA' };
const TOTALE_DOMANDE = Object.keys(DOMANDE).length;
const ATTESA_MINIMA_MS = 3000; // la rotellina resta almeno 3 secondi, anche se l'API risponde prima

const OPZIONI_IVA = [
  { valore: true, etichetta: 'Sì, IVA inclusa', sotto: "Il totale del preventivo comprende già l'IVA" },
  { valore: false, etichetta: 'No, IVA esclusa', sotto: "Al totale va aggiunta l'IVA (22%)" },
];

export default function App() {
  const [step, setStep] = useState(BENVENUTO);
  // ogni passo è anche una voce della cronologia del browser: il gesto "indietro" del telefono
  // torna alla domanda precedente invece di uscire dal sito (indice = posizione del passo nella cronologia)
  const indice = useRef(0);
  // elenchi del form dall'API (regioni, interventi, marche, modelli e versioni, qualità, tipi di officina, data dei prezzi)
  const [opzioni, setOpzioni] = useState(null);
  const [erroreOpzioni, setErroreOpzioni] = useState(null);
  const [erroreVerifica, setErroreVerifica] = useState(null);
  const [regione, setRegione] = useState(null);
  const [auto, setAuto] = useState(null); // { marca, modello, variante }
  const [anno, setAnno] = useState(''); // facoltativo
  const [intervento, setIntervento] = useState(null);
  const [altro, setAltro] = useState(''); // intervento descritto a parole, se non è in elenco
  const [qualita, setQualita] = useState(null);
  const [officina, setOfficina] = useState(null);
  const [prezzo, setPrezzo] = useState('');
  const [ivaInclusa, setIvaInclusa] = useState(null);
  const [result, setResult] = useState(null);
  const verificaInCorso = useRef(0); // per ignorare la risposta di una verifica annullata
  const haRisultato = useRef(false);

  useEffect(() => {
    // lo scorrimento lo decide il sito (ogni schermata parte dall'alto), non il browser
    window.history.scrollRestoration = 'manual';
    window.history.replaceState({ passo: BENVENUTO, indice: 0 }, '');
    function quandoTornaIndietro(e) {
      const s = e.state;
      if (!s || typeof s.passo !== 'number') return;
      indice.current = s.indice ?? 0;
      verificaInCorso.current += 1; // uscendo dall'attesa, la verifica in corso si annulla
      // attesa e report non si riaprono senza un risultato: si torna all'ultima domanda
      setStep(s.passo >= ATTESA && !haRisultato.current ? IVA : s.passo);
    }
    window.addEventListener('popstate', quandoTornaIndietro);
    return () => window.removeEventListener('popstate', quandoTornaIndietro);
  }, []);

  // a ogni cambio di schermata si riparte dall'alto della pagina
  useEffect(() => { window.scrollTo(0, 0); }, [step]);

  function avanza(passo) {
    indice.current += 1;
    window.history.pushState({ passo, indice: indice.current }, '');
    setStep(passo);
  }
  const indietro = () => window.history.back();

  const prezzoNum = Number(String(prezzo).replace(',', '.')) || 0;
  const nonPrevisti = auto?.variante?.interventi_non_previsti || [];

  const leggiOpzioni = useCallback(() => {
    caricaOpzioni().then(setOpzioni).catch((e) => setErroreOpzioni(e.message));
  }, []);
  useEffect(leggiOpzioni, [leggiOpzioni]);
  function riprovaOpzioni() {
    setErroreOpzioni(null);
    leggiOpzioni();
  }

  // elenco unico delle versioni, come la lista auto della V1: arrivano con gli elenchi del form, in una sola chiamata
  const versioni = useMemo(() => opzioni && opzioni.marche.flatMap((marca) => marca.modelli.flatMap((modello) =>
    modello.varianti.map((variante) => ({ marca, modello, variante })))), [opzioni]);

  // cambiando versione, un intervento non previsto per la nuova versione si toglie
  function scegliAuto(a) {
    setAuto(a);
    // un anno scelto per un'altra versione, se questa non era in produzione quell'anno, si toglie
    if (a && anno && !anniDiProduzione(a.variante).includes(anno)) setAnno('');
    if (a && intervento && (a.variante.interventi_non_previsti || []).includes(intervento.id)) setIntervento(null);
  }

  async function goVerify() {
    setErroreVerifica(null);
    avanza(ATTESA);
    const id = ++verificaInCorso.current;
    try {
      const [r] = await Promise.all([
        verifica({
          variante_id: auto?.variante?.id,
          anno_immatricolazione: anno.length === 4 ? Number(anno) : null,
          intervento_id: intervento?.id ?? null,
          altro_intervento: intervento ? null : altro.trim(),
          qualita,
          regione_id: regione?.id,
          tipo_officina: officina,
          importo_preventivo: prezzoNum,
          iva_inclusa: ivaInclusa,
        }),
        new Promise((fatto) => setTimeout(fatto, ATTESA_MINIMA_MS)),
      ]);
      if (id !== verificaInCorso.current) return;
      setResult(r);
      haRisultato.current = true;
      // il report prende il posto dell'attesa: "indietro" dal report riporta all'ultima domanda
      window.history.replaceState({ passo: REPORT, indice: indice.current }, '');
      setStep(REPORT);
    } catch (e) {
      if (id !== verificaInCorso.current) return;
      setErroreVerifica(e.message);
      indietro();
    }
  }

  function annullaVerifica() {
    indietro();
  }

  function reset() {
    // si torna al benvenuto riavvolgendo la cronologia del sito
    haRisultato.current = false;
    if (indice.current > 0) window.history.go(-indice.current);
    else setStep(BENVENUTO);
    setRegione(null);
    setAuto(null);
    setAnno('');
    setIntervento(null);
    setAltro('');
    setQualita(null);
    setOfficina(null);
    setPrezzo('');
    setIvaInclusa(null);
    setResult(null);
    setErroreVerifica(null);
  }

  const vai = (passo) => () => avanza(passo);

  return (
    <div className="phone">
      <Header right={step === ATTESA ? 'Analisi…' : null} onBack={DOMANDE[step] || step === REPORT ? indietro : null} />
      {DOMANDE[step] && <Avanzamento passo={step} totale={TOTALE_DOMANDE} nome={DOMANDE[step]} />}
      <main className="phone-main">
        {step === BENVENUTO && <Welcome prezziAl={opzioni?.prezzi_ricambi_al} onStart={vai(REGIONE)} />}
        {step === REGIONE && (
          <Regione regioni={opzioni?.regioni} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={regione} onSelect={setRegione} onNext={vai(AUTO)} onBack={indietro} />
        )}
        {step === AUTO && (
          <Auto versioni={versioni} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={auto} onSelect={scegliAuto} onNext={vai(ANNO)} onBack={indietro} />
        )}
        {step === ANNO && <Anno auto={auto} value={anno} onChange={setAnno} onNext={vai(INTERVENTO)} onBack={indietro} />}
        {step === INTERVENTO && (
          <Intervento
            interventi={opzioni?.interventi}
            errore={erroreOpzioni}
            onRiprova={riprovaOpzioni}
            nonPrevisti={nonPrevisti}
            value={intervento}
            altro={altro}
            onSelect={setIntervento}
            onAltro={setAltro}
            onNext={vai(QUALITA)}
            onBack={indietro}
          />
        )}
        {step === QUALITA && (
          <SceltaSingola
            titolo="Che ricambi monterà l'officina?"
            spiegazione="Se non lo sai, chiedilo all'officina: di solito è scritto sul preventivo."
            opzioni={opzioni?.qualita}
            value={qualita}
            onSelect={setQualita}
            onNext={vai(OFFICINA)}
            onBack={indietro}
          />
        )}
        {step === OFFICINA && (
          <SceltaSingola
            titolo="Dove fai l'intervento?"
            spiegazione="La tariffa oraria cambia molto tra concessionaria e officina indipendente."
            opzioni={opzioni?.tipi_officina}
            value={officina}
            onSelect={setOfficina}
            onNext={vai(PREZZO)}
            onBack={indietro}
          />
        )}
        {step === PREZZO && (
          <Prezzo regione={regione} auto={auto} intervento={intervento} altro={altro} prezzo={prezzo} onPrezzo={setPrezzo} onNext={vai(IVA)} onBack={indietro} />
        )}
        {step === IVA && (
          <SceltaSingola
            titolo="L'importo comprende l'IVA?"
            opzioni={OPZIONI_IVA}
            value={ivaInclusa}
            errore={erroreVerifica}
            testoAvanti="Verifica Preventivo →"
            onSelect={setIvaInclusa}
            onNext={goVerify}
            onBack={indietro}
          />
        )}
        {step === ATTESA && <Loading onCancel={annullaVerifica} />}
        {step === REPORT && result && (
          <Report auto={auto} intervento={intervento} result={result} prezziAl={opzioni?.prezzi_ricambi_al} onReset={reset} />
        )}
      </main>
    </div>
  );
}
