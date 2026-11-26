import { MAPPA_ALTEZZA, MAPPA_LARGHEZZA, REGIONI_MAPPA } from '../../data/mappaItalia.js';
import './MappaItalia.css';

// "Valle d'Aosta/Vallée d'Aoste" e "Valle d'Aosta" diventano la stessa chiave
const chiave = (nome) => String(nome).split('/')[0].normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z]/g, '');

// L'Italia con le 20 regioni (solo su computer): la regione evidenziata è verde menta e illuminata.
// Cliccando una regione la si sceglie; la ruota accanto gira fino a lei.
// regioni: l'elenco dall'API · evidenziata: la regione al centro della ruota · onScegli(regione)
export default function MappaItalia({ regioni = [], evidenziata, onScegli }) {
  const perChiave = Object.fromEntries(regioni.map((r) => [chiave(r.nome), r]));
  const attiva = evidenziata ? chiave(evidenziata.nome) : null;
  return (
    <figure className="mappa">
      <svg viewBox={`0 0 ${MAPPA_LARGHEZZA} ${MAPPA_ALTEZZA}`} className="mappa-svg" aria-hidden="true">
        {REGIONI_MAPPA.map((m) => {
          const k = chiave(m.nome);
          const r = perChiave[k];
          if (k === attiva) return null; // la regione attiva si disegna per ultima, sopra le altre
          return <path key={m.codice} d={m.d} className="mappa-regione" onClick={r ? () => onScegli(r) : undefined} />;
        })}
        {REGIONI_MAPPA.filter((m) => chiave(m.nome) === attiva).map((m) => (
          <path key={m.codice} d={m.d} className="mappa-regione is-attiva" />
        ))}
      </svg>
      <figcaption className="mappa-didascalia">
        <span className="mappa-nome" aria-live="polite">{evidenziata?.nome || ' '}</span>
        <span className="mappa-fonte">Confini: openpolis, dati ISTAT (CC BY 4.0)</span>
      </figcaption>
    </figure>
  );
}
