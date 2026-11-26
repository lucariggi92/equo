import Domanda from '../../components/Domanda/Domanda.jsx';
import { euro } from '../../lib/format.js';
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

// L'importo e, subito sotto, se comprende l'IVA (un selettore a due pulsanti al posto di una domanda a parte).
// Un campo numerico: sul telefono apre la tastiera con i numeri; il tasto Invio della tastiera vale come Avanti.
export default function Prezzo({ prezzo, onPrezzo, ivaInclusa, onIva, onNext, onBack }) {
  const importo = prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.'));
  const valido = importo > 0;
  const completo = valido && ivaInclusa !== null && ivaInclusa !== undefined;
  return (
    <Domanda titolo="Quanto ti ha chiesto il meccanico?" onBack={onBack} onNext={onNext} avantiDisabilitato={!completo}>
      {/* il campo sta in cima: con la tastiera del telefono aperta resta sempre visibile */}
      <form
        className="importo"
        onSubmit={(e) => { e.preventDefault(); if (completo) onNext(); }}
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
        {/* IVA inclusa o esclusa: due pulsanti, uno solo acceso */}
        <div className="iva-scelta" role="radiogroup" aria-label="L'importo comprende l'IVA?">
          {[[true, 'IVA inclusa'], [false, 'IVA esclusa']].map(([valore, testo]) => (
            <button
              key={testo}
              type="button"
              role="radio"
              aria-checked={ivaInclusa === valore}
              className={`iva-pulsante${ivaInclusa === valore ? ' is-attivo' : ''}`}
              onClick={() => onIva(valore)}
            >
              {testo}
            </button>
          ))}
        </div>
        <p id="importo-nota" className="importo-nota">
          {!valido && 'Scrivi il totale che trovi sul preventivo, anche con i centesimi.'}
          {valido && ivaInclusa == null && <>Hai scritto <strong className="numeric">{euro(importo)}</strong>: l'IVA è inclusa?</>}
          {valido && ivaInclusa === true && <>Hai scritto <strong className="numeric">{euro(importo)}</strong>, IVA inclusa</>}
          {valido && ivaInclusa === false && <>Hai scritto <strong className="numeric">{euro(importo)}</strong> più IVA (22%)</>}
        </p>
      </form>
    </Domanda>
  );
}
