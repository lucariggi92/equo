import { useEffect, useId, useLayoutEffect, useRef, useState } from 'react';
import SearchBar from '../SearchBar/SearchBar.jsx';
import './Ruota.css';

const FINE_SCORRIMENTO = typeof window !== 'undefined' && 'onscrollend' in window;

// per la ricerca: minuscole e senza accenti ("Valle d'Aosta" si trova anche con "valle d aosta")
const semplice = (t) => String(t ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

// Ruota di scelta (come quelle del telefono): la lista scorre in verticale dentro una lente fissa al centro.
// La voce nella lente è quella scelta: è grande e piena; le altre rimpiccioliscono e sfumano allontanandosi.
// opzioni: [{ chiave, etichetta, sotto?, dato }] · valore: la chiave scelta · onChange(dato) quando la ruota si ferma.
// Con altezza 'alta' ogni riga ha spazio per una seconda riga di testo (sotto).
// Con 'cerca' (il testo segnaposto) sopra la ruota c'è la casella di ricerca: filtra le voci e porta al centro
// la prima trovata, oppure resta sulla voce già scelta se è tra i risultati.
// onCentro(dato), facoltativo: chiamata in tempo reale mentre la ruota gira, con la voce che passa nella lente.
export default function Ruota({ opzioni, valore, onChange, onCentro, etichetta, alta = false, cerca }) {
  const lista = useRef(null);
  const fermo = useRef(null);
  const id = useId();
  const riga = alta ? 68 : 56;
  const [q, setQ] = useState('');
  const visibili = q.trim()
    ? opzioni.filter((o) => semplice(`${o.etichetta} ${o.sotto || ''}`).includes(semplice(q.trim())))
    : opzioni;
  const iniziale = Math.max(0, visibili.findIndex((o) => o.chiave === valore));
  const [centro, setCentro] = useState(iniziale);

  // voci, scelta e callback sempre aggiornate per il gestore di fine scorrimento
  const ultimo = useRef({ visibili, valore, onChange });
  ultimo.current = { visibili, valore, onChange };

  function indice() {
    const el = lista.current;
    if (!el) return 0;
    return Math.max(0, Math.min(ultimo.current.visibili.length - 1, Math.round(el.scrollTop / riga)));
  }

  // la ruota si è fermata: la voce al centro diventa la scelta
  function scegli() {
    const { visibili: o, valore: v, onChange: cambia } = ultimo.current;
    const scelta = o[indice()];
    if (scelta && scelta.chiave !== v) cambia(scelta.dato);
  }

  function quandoScorre() {
    setCentro(indice());
    // la scelta parte comunque dopo una breve pausa, anche se il browser non segnala la fine dello scorrimento
    clearTimeout(fermo.current);
    fermo.current = setTimeout(scegli, FINE_SCORRIMENTO ? 400 : 250);
  }

  function vai(i, modo = 'smooth') {
    const el = lista.current;
    if (!el) return;
    const j = Math.max(0, Math.min(visibili.length - 1, i));
    el.scrollTo({ top: j * riga, behavior: modo });
  }

  // toccando una voce la scelta vale subito, poi la voce scivola nella lente
  function tocca(i) {
    setCentro(i);
    const o = visibili[i];
    if (o && o.chiave !== valore) onChange(o.dato);
    vai(i);
  }

  // all'apertura: la voce già scelta al centro; se non c'è una scelta, vale la prima voce
  useLayoutEffect(() => {
    vai(iniziale, 'instant');
    setCentro(iniziale);
    const scelta = visibili[iniziale];
    if (scelta && scelta.chiave !== valore) onChange(scelta.dato);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // dopo ogni ricerca: resta sulla voce scelta se è tra i risultati, altrimenti sceglie la prima trovata
  const primaRicerca = useRef(true);
  useLayoutEffect(() => {
    if (primaRicerca.current) { primaRicerca.current = false; return; }
    if (!visibili.length) return;
    const i = Math.max(0, visibili.findIndex((o) => o.chiave === valore));
    vai(i, 'instant');
    setCentro(i);
    if (visibili[i].chiave !== valore) onChange(visibili[i].dato);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q]);

  // la voce nella lente, in tempo reale, a chi la vuole mostrare (per esempio la mappa)
  const voceCentro = visibili[centro];
  useEffect(() => {
    if (onCentro && voceCentro) onCentro(voceCentro.dato);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [voceCentro?.chiave]);

  // la scelta è cambiata da fuori (per esempio cliccando la mappa): la ruota gira fino a lei
  useEffect(() => {
    const i = visibili.findIndex((o) => o.chiave === valore);
    if (i >= 0 && i !== indice()) {
      setCentro(i);
      vai(i);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [valore]);

  useEffect(() => {
    const el = lista.current;
    if (FINE_SCORRIMENTO && el) el.addEventListener('scrollend', scegli);
    return () => {
      clearTimeout(fermo.current);
      if (el) el.removeEventListener('scrollend', scegli);
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // tastiera: frecce su e giù, inizio e fine
  function tasto(e) {
    const mosse = { ArrowDown: centro + 1, ArrowUp: centro - 1, Home: 0, End: visibili.length - 1, PageDown: centro + 4, PageUp: centro - 4 };
    if (e.key in mosse) {
      e.preventDefault();
      vai(mosse[e.key]);
    }
  }

  return (
    <div className="ruota-blocco">
      {cerca && <SearchBar value={q} onChange={setQ} placeholder={cerca} />}
      <div className={`ruota${alta ? ' ruota-alta' : ''}`} style={{ '--riga': `${riga}px` }}>
        <div className="ruota-lente" aria-hidden="true" />
        {!visibili.length && <p className="ruota-vuota">Nessun risultato per "{q.trim()}"</p>}
        <div
          ref={lista}
          className="ruota-lista"
          role="listbox"
          tabIndex={0}
          aria-label={etichetta}
          aria-activedescendant={visibili.length ? `${id}-${centro}` : undefined}
          onScroll={quandoScorre}
          onKeyDown={tasto}
        >
          {visibili.map((o, i) => {
            const distanza = Math.min(3, Math.abs(i - centro));
            return (
              <div
                key={o.chiave}
                id={`${id}-${i}`}
                role="option"
                aria-selected={i === centro}
                className={`ruota-voce ruota-d${distanza}`}
                onClick={() => tocca(i)}
              >
                <span className="ruota-etichetta">{o.etichetta}</span>
                {o.sotto && <span className="ruota-sotto">{o.sotto}</span>}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}
