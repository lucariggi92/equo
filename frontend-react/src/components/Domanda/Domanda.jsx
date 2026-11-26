import Button from '../Button/Button.jsx';
import './Domanda.css';

// Impaginazione comune a tutte le domande:
// in alto, fissi, la domanda, la spiegazione e (se c'è) la ricerca;
// in mezzo il contenuto, l'unica parte che scorre (elenco, ruota, tendina, tastierino);
// in fondo, sempre visibili, Indietro e Avanti.
// 'lato' (facoltativo): un contenuto mostrato a destra solo su computer, per esempio la mappa delle regioni.
export default function Domanda({ titolo, spiegazione, fisso, lato, children, onBack, onNext, avantiDisabilitato = false, testoAvanti = 'Avanti' }) {
  return (
    <div className={`screen domanda${lato ? ' ha-lato' : ''}`}>
      <div className="domanda-testa">
        <h1 className="h1">{titolo}</h1>
        {spiegazione && <p className="muted">{spiegazione}</p>}
        {fisso}
      </div>
      <div className="domanda-corpo">{children}</div>
      <div className="domanda-piede stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={avantiDisabilitato} onClick={onNext}>{testoAvanti}</Button>
      </div>
      {lato && <aside className="domanda-lato">{lato}</aside>}
    </div>
  );
}
