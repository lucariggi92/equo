import { useState } from 'react';
import Header from './components/Header/Header.jsx';
import Welcome from './screens/Welcome/Welcome.jsx';
import Regione from './screens/Regione/Regione.jsx';
import Auto from './screens/Auto/Auto.jsx';
import Intervento from './screens/Intervento/Intervento.jsx';
import Prezzo from './screens/Prezzo/Prezzo.jsx';
import Loading from './screens/Loading/Loading.jsx';
import Report from './screens/Report/Report.jsx';
import { verificaPreventivo } from './lib/api.js';
import './App.css';

export default function App() {
  const [step, setStep] = useState(0);
  const [regione, setRegione] = useState(null);
  const [auto, setAuto] = useState(null);
  const [intervento, setIntervento] = useState(null);
  const [note, setNote] = useState('');
  const [prezzo, setPrezzo] = useState('');
  const [result, setResult] = useState(null);

  const prezzoNum = Number(String(prezzo).replace(',', '.')) || 0;

  async function goVerify() {
    setStep(5);
    try {
      const r = await verificaPreventivo({ regione, auto, intervento, prezzo: prezzoNum, note });
      setResult(r);
      setStep(6);
    } catch (e) {
      setResult({ manodopera: 0, ricambi: 0, stima: 0, scostamento: 0, verdetto: 'sopra-media', fonte: 'errore', errore: String(e.message || e) });
      setStep(6);
    }
  }

  function reset() {
    setStep(0);
    setRegione(null);
    setAuto(null);
    setIntervento(null);
    setNote('');
    setPrezzo('');
    setResult(null);
  }

  return (
    <div className="phone">
      <Header right={step === 5 ? 'Analisi...' : null} />
      <main className="phone-main">
        {step === 0 && <Welcome onStart={() => setStep(1)} />}
        {step === 1 && (
          <Regione value={regione} onSelect={setRegione} onNext={() => setStep(2)} onBack={() => setStep(0)} />
        )}
        {step === 2 && (
          <Auto value={auto} onSelect={setAuto} onNext={() => setStep(3)} onBack={() => setStep(1)} />
        )}
        {step === 3 && (
          <Intervento
            value={intervento}
            note={note}
            onSelect={setIntervento}
            onNote={setNote}
            onNext={() => setStep(4)}
            onBack={() => setStep(2)}
          />
        )}
        {step === 4 && (
          <Prezzo
            regione={regione}
            auto={auto}
            intervento={intervento}
            prezzo={prezzo}
            onPrezzo={setPrezzo}
            onNext={goVerify}
            onBack={() => setStep(3)}
          />
        )}
        {step === 5 && <Loading onCancel={() => setStep(4)} />}
        {step === 6 && result && (
          <Report regione={regione} auto={auto} intervento={intervento} prezzo={prezzoNum} result={result} onReset={reset} />
        )}
      </main>
    </div>
  );
}
