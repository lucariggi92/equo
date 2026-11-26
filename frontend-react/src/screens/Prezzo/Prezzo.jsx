import Domanda from '../../components/Domanda/Domanda.jsx';
import Card from '../../components/Card/Card.jsx';
import { euro } from '../../lib/format.js';
import { nomeVisibile } from '../../lib/versioni.js';
import '../Welcome/Welcome.css';
import './Prezzo.css';

// Tiene solo cifre e una virgola (il punto diventa virgola), al massimo 6 cifre intere e 2 decimali, come accetta l'API.
function pulisci(testo) {
  const t = String(testo).replace(/\./g, ',').replace(/[^\d,]/g, '');
  const [intero = '', ...resto] = t.split(',');
  const interoPulito = intero.replace(/^0+(?=\d)/, '').slice(0, 6);
  if (!resto.length) return interoPulito;
  return `${interoPulito || '0'},${resto.join('').slice(0, 2)}`;
}

// Solo l'importo: la domanda sull'IVA è la schermata successiva.
// Un campo numerico: sul telefono apre la tastiera con i numeri; il tasto Invio della tastiera vale come Avanti.
export default function Prezzo({ regione, auto, intervento, altro, prezzo, onPrezzo, onNext, onBack }) {
  const importo = prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.'));
  const valido = importo > 0;
  return (
    <Domanda titolo="Quanto ti ha chiesto il meccanico?" onBack={onBack} onNext={onNext} avantiDisabilitato={!valido}>
      <Card title="Riepilogo">
        <div className="recap recap-table">
          <span>Regione: <strong>{regione?.nome}</strong></span>
          <span>Veicolo: <strong>{nomeVisibile(auto)}</strong></span>
          <span>Intervento: <strong>{intervento?.nome || altro}</strong></span>
        </div>
      </Card>
      <form
        className="importo"
        onSubmit={(e) => { e.preventDefault(); if (valido) onNext(); }}
      >
        <label className="importo-etichetta" htmlFor="importo-campo">Importo del preventivo</label>
        <div className="importo-campo">
          <span className="importo-valuta" aria-hidden="true">€</span>
          <input
            id="importo-campo"
            className="importo-input numeric"
            type="text"
            inputMode="decimal"
            enterKeyHint="next"
            autoComplete="off"
            autoFocus
            placeholder="0,00"
            value={prezzo}
            onChange={(e) => onPrezzo(pulisci(e.target.value))}
            aria-describedby="importo-nota"
          />
        </div>
        <p id="importo-nota" className="importo-nota">
          {valido ? <>Hai scritto <strong className="numeric">{euro(importo)}</strong></> : 'Scrivi il totale che trovi sul preventivo, anche con i centesimi.'}
        </p>
      </form>
    </Domanda>
  );
}
