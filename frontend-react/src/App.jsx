import { useCallback, useEffect, useRef, useState } from 'react';
import Header from './components/Header/Header.jsx';
import Welcome from './screens/Welcome/Welcome.jsx';
import Regione from './screens/Regione/Regione.jsx';
import Auto from './screens/Auto/Auto.jsx';
import Intervento from './screens/Intervento/Intervento.jsx';
import Scelte from './screens/Scelte/Scelte.jsx';
import Prezzo from './screens/Prezzo/Prezzo.jsx';
import Loading from './screens/Loading/Loading.jsx';
import Report from './screens/Report/Report.jsx';
import { opzioni as caricaOpzioni, verifica } from './api/client.js';
import './App.css';

// passi del form
const BENVENUTO = 0, REGIONE = 1, AUTO = 2, INTERVENTO = 3, SCELTE = 4, PREZZO = 5, ATTESA = 6, REPORT = 7;

export default function App() {
  const [step, setStep] = useState(BENVENUTO);
  // elenchi del form dall'API (regioni, interventi, marche e modelli, qualità, tipi di officina, data dei prezzi)
  const [opzioni, setOpzioni] = useState(null);
  const [erroreOpzioni, setErroreOpzioni] = useState(null);
  const [erroreVerifica, setErroreVerifica] = useState(null);
  const [regione, setRegione] = useState(null);
  const [auto, setAuto] = useState(null); // { marca, modello, variante, anno }
  const [intervento, setIntervento] = useState(null);
  const [altro, setAltro] = useState(''); // intervento descritto a parole, se non è in elenco
  const [qualita, setQualita] = useState(null);
  const [officina, setOfficina] = useState(null);
  const [prezzo, setPrezzo] = useState('');
  const [ivaInclusa, setIvaInclusa] = useState(null);
  const [result, setResult] = useState(null);
  const verificaInCorso = useRef(0); // per ignorare la risposta di una verifica annullata

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

  // cambiando versione, un intervento non previsto per la nuova versione si toglie
  function scegliAuto(a) {
    setAuto(a);
    if (a && intervento && (a.variante.interventi_non_previsti || []).includes(intervento.id)) setIntervento(null);
  }

  async function goVerify() {
    const id = ++verificaInCorso.current;
    setStep(ATTESA);
    setErroreVerifica(null);
    try {
      const r = await verifica({
        variante_id: auto?.variante?.id,
        anno_immatricolazione: auto?.anno ?? null,
        intervento_id: intervento?.id ?? null,
        altro_intervento: intervento ? null : altro.trim(),
        qualita,
        regione_id: regione?.id,
        tipo_officina: officina,
        importo_preventivo: prezzoNum,
        iva_inclusa: ivaInclusa,
      });
      if (id !== verificaInCorso.current) return;
      setResult(r);
      setStep(REPORT);
    } catch (e) {
      if (id !== verificaInCorso.current) return;
      setErroreVerifica(e.message);
      setStep(PREZZO);
    }
  }

  function annullaVerifica() {
    verificaInCorso.current += 1;
    setStep(PREZZO);
  }

  function reset() {
    setStep(BENVENUTO);
    setRegione(null);
    setAuto(null);
    setIntervento(null);
    setAltro('');
    setQualita(null);
    setOfficina(null);
    setPrezzo('');
    setIvaInclusa(null);
    setResult(null);
    setErroreVerifica(null);
  }

  const etichetta = (elenco, valore) => (elenco || []).find((x) => x.valore === valore);

  return (
    <div className="phone">
      <Header right={step === ATTESA ? 'Analisi...' : null} />
      <main className="phone-main">
        {step === BENVENUTO && <Welcome prezziAl={opzioni?.prezzi_ricambi_al} onStart={() => setStep(REGIONE)} />}
        {step === REGIONE && (
          <Regione regioni={opzioni?.regioni} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={regione} onSelect={setRegione} onNext={() => setStep(AUTO)} onBack={() => setStep(BENVENUTO)} />
        )}
        {step === AUTO && (
          <Auto marche={opzioni?.marche} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={auto} onSelect={scegliAuto} onNext={() => setStep(INTERVENTO)} onBack={() => setStep(REGIONE)} />
        )}
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
            onNext={() => setStep(SCELTE)}
            onBack={() => setStep(AUTO)}
          />
        )}
        {step === SCELTE && (
          <Scelte
            qualita={opzioni?.qualita}
            tipiOfficina={opzioni?.tipi_officina}
            valueQualita={qualita}
            valueOfficina={officina}
            onQualita={setQualita}
            onOfficina={setOfficina}
            onNext={() => setStep(PREZZO)}
            onBack={() => setStep(INTERVENTO)}
          />
        )}
        {step === PREZZO && (
          <Prezzo
            regione={regione}
            auto={auto}
            intervento={intervento}
            altro={altro}
            qualita={etichetta(opzioni?.qualita, qualita)}
            officina={etichetta(opzioni?.tipi_officina, officina)}
            prezzo={prezzo}
            ivaInclusa={ivaInclusa}
            errore={erroreVerifica}
            onPrezzo={setPrezzo}
            onIva={setIvaInclusa}
            onNext={goVerify}
            onBack={() => setStep(SCELTE)}
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
