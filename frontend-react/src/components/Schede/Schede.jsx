import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import './Schede.css';

const FINE_SCORRIMENTO = typeof window !== 'undefined' && 'onscrollend' in window;

// Schede orizzontali con la foto, da scorrere col dito: la scheda al centro è grande e illuminata ed è la scelta.
// opzioni: [{ chiave, titolo, sotto, foto: { src, autore, licenza, pagina } | null, dato }]
// valore: la chiave scelta · onChange(dato) quando lo scorrimento si ferma (o si tocca una scheda).
// Sotto le schede: i crediti della foto al centro (le licenze CC BY-SA chiedono di citare autore e licenza).
export default function Schede({ opzioni, valore, onChange, etichetta }) {
  const binario = useRef(null);
  const fermo = useRef(null);
  const iniziale = Math.max(0, opzioni.findIndex((o) => o.chiave === valore));
  const [centro, setCentro] = useState(iniziale);
  const ultimo = useRef({ opzioni, valore, onChange });
  ultimo.current = { opzioni, valore, onChange };

  function sinistraPer(i) {
    const el = binario.current;
    const s = el?.children[i];
    return s ? s.offsetLeft + s.offsetWidth / 2 - el.clientWidth / 2 : 0;
  }
  function indice() {
    const el = binario.current;
    if (!el || !el.children.length) return 0;
    const meta = el.scrollLeft + el.clientWidth / 2;
    let migliore = 0;
    let distanza = Infinity;
    [...el.children].forEach((s, i) => {
      const d = Math.abs(s.offsetLeft + s.offsetWidth / 2 - meta);
      if (d < distanza) { distanza = d; migliore = i; }
    });
    return migliore;
  }
  function scegli() {
    const { opzioni: o, valore: v, onChange: cambia } = ultimo.current;
    const s = o[indice()];
    if (s && s.chiave !== v) cambia(s.dato);
  }
  function quandoScorre() {
    setCentro(indice());
    // la scelta parte comunque dopo una breve pausa, anche se il browser non segnala la fine dello scorrimento
    clearTimeout(fermo.current);
    fermo.current = setTimeout(scegli, FINE_SCORRIMENTO ? 400 : 250);
  }
  function vai(i, modo = 'smooth') {
    const j = Math.max(0, Math.min(opzioni.length - 1, i));
    binario.current?.scrollTo({ left: sinistraPer(j), behavior: modo });
  }
  // toccando una scheda la scelta vale subito, poi la scheda scivola al centro
  function tocca(i) {
    setCentro(i);
    const o = opzioni[i];
    if (o && o.chiave !== valore) onChange(o.dato);
    vai(i);
  }
  ultimo.current.centro = centro;
  ultimo.current.tocca = tocca;

  // all'apertura e a ogni nuova ricerca: la scheda scelta (o la prima) al centro
  const chiavi = opzioni.map((o) => o.chiave).join('|');
  useLayoutEffect(() => {
    if (!opzioni.length) return;
    const i = Math.max(0, opzioni.findIndex((o) => o.chiave === valore));
    vai(i, 'instant');
    setCentro(i);
    if (opzioni[i].chiave !== valore) onChange(opzioni[i].dato);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [chiavi]);

  useEffect(() => {
    const el = binario.current;
    if (!el) return undefined;
    if (FINE_SCORRIMENTO) el.addEventListener('scrollend', scegli);
    // rotella del mouse (su e giù): una scheda per scatto, come scorrere col dito
    let fermoRotella = 0;
    function rotella(e) {
      if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return; // il trackpad orizzontale scorre da solo
      const { centro: c, opzioni: o, tocca: t } = ultimo.current;
      const prossima = c + (e.deltaY > 0 ? 1 : -1);
      if (prossima < 0 || prossima >= o.length) return; // ai bordi la pagina scorre normalmente
      e.preventDefault();
      const ora = Date.now();
      if (ora - fermoRotella < 350) return;
      fermoRotella = ora;
      t(prossima);
    }
    el.addEventListener('wheel', rotella, { passive: false });
    return () => {
      clearTimeout(fermo.current);
      el.removeEventListener('scrollend', scegli);
      el.removeEventListener('wheel', rotella);
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function tasto(e) {
    if (e.key === 'ArrowRight') { e.preventDefault(); tocca(Math.min(opzioni.length - 1, centro + 1)); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); tocca(Math.max(0, centro - 1)); }
  }

  const attiva = opzioni[centro];
  return (
    <div className="schede">
      <div
        ref={binario}
        className="schede-binario"
        role="listbox"
        aria-label={etichetta}
        aria-orientation="horizontal"
        tabIndex={0}
        onScroll={quandoScorre}
        onKeyDown={tasto}
      >
        {opzioni.map((o, i) => (
          <div
            key={o.chiave}
            role="option"
            aria-selected={i === centro}
            className={`scheda-foto${i === centro ? ' is-attiva' : ''}`}
            onClick={() => tocca(i)}
          >
            <div className="scheda-foto-immagine">
              {o.foto
                ? <img src={o.foto.src} alt={`${o.titolo}, foto`} loading="lazy" decoding="async" />
                : <span className="scheda-foto-vuota" aria-hidden="true">{o.titolo}</span>}
            </div>
            <div className="scheda-foto-testo">
              <span className="scheda-foto-titolo">{o.titolo}</span>
              {o.sotto && <span className="scheda-foto-sotto">{o.sotto}</span>}
            </div>
          </div>
        ))}
      </div>
      {opzioni.length > 1 && (
        <p className="schede-conta numeric" aria-live="polite">{centro + 1} di {opzioni.length}</p>
      )}
      {attiva?.foto && (
        <p className="schede-crediti">
          Foto: {attiva.foto.autore}, <a href={attiva.foto.pagina} target="_blank" rel="noreferrer">Wikimedia Commons</a>, {attiva.foto.licenza}
        </p>
      )}
    </div>
  );
}
