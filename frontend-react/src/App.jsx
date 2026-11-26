import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import Header from './components/Header/Header.jsx';
import Avanzamento from './components/Avanzamento/Avanzamento.jsx';
import Footer from './components/Footer/Footer.jsx';
import Welcome from './screens/Welcome/Welcome.jsx';
import Regione from './screens/Regione/Regione.jsx';
import Marca from './screens/Marca/Marca.jsx';
import Auto from './screens/Auto/Auto.jsx';
import Anno from './screens/Anno/Anno.jsx';
import Versione from './screens/Versione/Versione.jsx';
import Intervento from './screens/Intervento/Intervento.jsx';
import SceltaSingola from './screens/SceltaSingola/SceltaSingola.jsx';
import Prezzo from './screens/Prezzo/Prezzo.jsx';
import Loading from './screens/Loading/Loading.jsx';
import Report from './screens/Report/Report.jsx';
import Riepilogo from './screens/Riepilogo/Riepilogo.jsx';
import { opzioni as caricaOpzioni, verifica } from './api/client.js';
import { anniDiProduzione } from './lib/format.js';
import { anniDelGruppo, versioniPerAnno } from './lib/versioni.js';
import './App.css';

// passi: benvenuto, 8 domande (una per schermata; l'IVA si sceglie nella schermata dell'importo), attesa, report
const BENVENUTO = 0, REGIONE = 1, MARCA = 2, AUTO = 3, ANNO = 4, INTERVENTO = 5, QUALITA = 6, OFFICINA = 7, PREZZO = 8,
  ATTESA = 10, REPORT = 11;
const DOMANDE = { [REGIONE]: 'regione', [MARCA]: 'marca', [AUTO]: 'modello', [ANNO]: 'anno', [INTERVENTO]: 'intervento', [QUALITA]: 'ricambi',
  [OFFICINA]: 'officina', [PREZZO]: 'importo' };
const TOTALE_DOMANDE = Object.keys(DOMANDE).length;
// passo in più, solo se l'anno non basta a capire la versione (restyling): sta tra anno e intervento,
// conta come la domanda sull'anno nella barra di avanzamento e resta sotto ATTESA (vedi il gesto indietro)
const VERSIONE = 4.5;
// pagina di riepilogo, dopo l'ultima domanda e prima dell'attesa: da qui parte la verifica
const RIEPILOGO = 8.5;
const eDomanda = (passo) => Boolean(DOMANDE[passo]) || passo === VERSIONE || passo === RIEPILOGO;
const ATTESA_MINIMA_MS = 3000; // la rotellina resta almeno 3 secondi, anche se l'API risponde prima


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
  const [marca, setMarca] = useState(null);
  const [gruppo, setGruppo] = useState(null); // la scheda scelta: un modello con una o più versioni (restyling)
  const [auto, setAuto] = useState(null); // la versione esatta: { marca, modello, variante }
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
      setStep(s.passo >= ATTESA && !haRisultato.current ? RIEPILOGO : s.passo);
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

  // versioni della marca scelta (modello sopra, motore sotto): arrivano con gli elenchi del form, in una sola chiamata
  const versioni = useMemo(() => (marca ? marca.modelli.flatMap((modello) =>
    modello.varianti.map((variante) => ({ marca, modello, variante }))) : []), [marca]);

  // cambiando marca, l'auto scelta per l'altra marca si toglie
  function scegliMarca(m) {
    setMarca(m);
    if (gruppo && gruppo.marca.nome !== m.nome) {
      setGruppo(null);
      setAuto(null);
      setAnno('');
    }
  }

  // scheda del modello: con una sola versione è già l'auto; con più versioni la sceglierà l'anno
  function scegliGruppo(g) {
    setGruppo(g);
    if (anno && !anniDelGruppo(g).includes(anno)) setAnno('');
    if (g.versioni.length === 1) scegliAuto(g.versioni[0]);
    else if (!g.versioni.some((a) => a.variante.id === auto?.variante?.id)) setAuto(null);
  }

  // dopo l'anno: se basta a capire la versione si va avanti, altrimenti si chiede (foto del dettaglio che cambia)
  const candidate = versioniPerAnno(gruppo, anno);
  function dopoAnno() {
    if (candidate.length === 1) {
      scegliAuto(candidate[0]);
      avanza(INTERVENTO);
    } else {
      if (!candidate.some((a) => a.variante.id === auto?.variante?.id)) setAuto(null);
      avanza(VERSIONE);
    }
  }

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
    setMarca(null);
    setGruppo(null);
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
    <div className={`phone${step === BENVENUTO ? ' is-home' : ''}${eDomanda(step) ? ' is-domanda' : ''}`}>
      <Header right={step === ATTESA ? 'Analisi…' : null} onBack={step === REPORT ? indietro : null} />
      {DOMANDE[step] && <Avanzamento passo={step} totale={TOTALE_DOMANDE} nome={DOMANDE[step]} />}
      {step === VERSIONE && <Avanzamento passo={ANNO} totale={TOTALE_DOMANDE} nome="versione" />}
      {step === RIEPILOGO && <Avanzamento passo={TOTALE_DOMANDE} totale={TOTALE_DOMANDE} testo="Riepilogo" />}
      <main className="phone-main">
        {step === BENVENUTO && <Welcome prezziAl={opzioni?.prezzi_ricambi_al} onStart={vai(REGIONE)} />}
        {step === REGIONE && (
          <Regione regioni={opzioni?.regioni} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={regione} onSelect={setRegione} onNext={vai(MARCA)} onBack={indietro} />
        )}
        {step === MARCA && (
          <Marca marche={opzioni?.marche} errore={erroreOpzioni} onRiprova={riprovaOpzioni} value={marca} onSelect={scegliMarca} onNext={vai(AUTO)} onBack={indietro} />
        )}
        {step === AUTO && (
          <Auto marca={marca} versioni={versioni} value={gruppo} onSelect={scegliGruppo} onNext={vai(ANNO)} onBack={indietro} />
        )}
        {step === ANNO && <Anno gruppo={gruppo} value={anno} onChange={setAnno} onNext={dopoAnno} onBack={indietro} />}
        {step === VERSIONE && (
          <Versione gruppo={gruppo} candidate={candidate} value={auto} onSelect={scegliAuto} onNext={vai(INTERVENTO)} onBack={indietro} />
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
            onNext={vai(QUALITA)}
            onBack={indietro}
          />
        )}
        {step === QUALITA && (
          <SceltaSingola
            titolo="Che ricambi monterà l'officina?"
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
            opzioni={opzioni?.tipi_officina}
            value={officina}
            onSelect={setOfficina}
            onNext={vai(PREZZO)}
            onBack={indietro}
          />
        )}
        {step === PREZZO && (
          <Prezzo prezzo={prezzo} onPrezzo={setPrezzo} ivaInclusa={ivaInclusa} onIva={setIvaInclusa} onNext={vai(RIEPILOGO)} onBack={indietro} />
        )}
        {step === RIEPILOGO && (
          <Riepilogo
            regione={regione}
            auto={auto}
            anno={anno}
            intervento={intervento}
            altro={altro}
            qualita={qualita}
            officina={officina}
            prezzo={prezzo}
            ivaInclusa={ivaInclusa}
            opzioni={opzioni}
            errore={erroreVerifica}
            onNext={goVerify}
            onBack={indietro}
          />
        )}
        {step === ATTESA && <Loading onCancel={annullaVerifica} />}
        {step === REPORT && result && (
          <Report auto={auto} intervento={intervento} result={result} prezziAl={opzioni?.prezzi_ricambi_al} onReset={reset} />
        )}
      </main>
      <Footer prezziAl={opzioni?.prezzi_ricambi_al} />
    </div>
  );
}
