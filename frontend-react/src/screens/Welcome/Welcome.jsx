import { useLayoutEffect, useRef, useState } from 'react';
import Button from '../../components/Button/Button.jsx';
import { data } from '../../lib/format.js';
import './Welcome.css';

// icone a tratto, una per scheda
const ICONE = {
  anonimo: <><path d="M12 3l8 3v6c0 4.5-3.4 8.2-8 9-4.6-.8-8-4.5-8-9V6z" /><path d="M9 12l2 2 4-4" /></>,
  regione: <><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></>,
  ricambi: <><circle cx="12" cy="12" r="3" /><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1" /></>,
  messaggio: <path d="M4 5h16v11H9l-5 4z" />,
};

// Giro infinito: le 4 schede sono ripetute 5 volte e si parte dalla copia di mezzo.
// Quando lo scorrimento si ferma lontano dal centro, si salta senza animazione sulla stessa scheda
// della copia di mezzo: chi scorre non se ne accorge e può continuare all'infinito nei due versi.
const GIRI = 5;
const MEZZO = Math.floor(GIRI / 2);
const FINE_SCORRIMENTO = typeof window !== 'undefined' && 'onscrollend' in window;

// Home (proposta A con lo sfondo della B): domanda, frase, quattro motivi da scorrere, pillola verde.
export default function Welcome({ prezziAl, onStart }) {
  const binario = useRef(null);
  const fermo = useRef(null);

  const schede = [
    { icona: 'anonimo', titolo: 'Gratis e anonimo', testo: 'Nessuna registrazione, nessuna targa.' },
    { icona: 'regione', titolo: 'Prezzi della tua regione', testo: 'Tariffe orarie stimate regione per regione.' },
    { icona: 'ricambi', titolo: 'Ricambi OEM e compatibili', testo: prezziAl ? `Prezzi medi aggiornati al ${data(prezziAl)}.` : 'Prezzi medi dei ricambi.' },
    { icona: 'messaggio', titolo: 'Messaggio pronto', testo: 'Una bozza cordiale da mandare al meccanico.' },
  ];
  const N = schede.length;
  const [centro, setCentro] = useState(MEZZO * N); // posizione, tra tutte le copie, della scheda al centro
  const attiva = centro % N;

  // distanza tra una scheda e la successiva
  function passo() {
    const el = binario.current;
    if (!el || el.children.length < 2) return 1;
    return el.children[1].offsetLeft - el.children[0].offsetLeft;
  }
  // scorrimento che mette la scheda i al centro del binario
  function sinistraPer(i) {
    const el = binario.current;
    const s = el.children[i];
    return s.offsetLeft + s.offsetWidth / 2 - el.clientWidth / 2;
  }
  // quale scheda sta al centro adesso
  function indiceAlCentro() {
    const el = binario.current;
    return Math.round((el.scrollLeft - sinistraPer(0)) / passo());
  }

  // salto invisibile sulla copia di mezzo (stessa scheda), senza animazioni
  function riportaAlMezzo() {
    const el = binario.current;
    if (!el) return;
    const i = indiceAlCentro();
    const giusto = MEZZO * N + (((i % N) + N) % N);
    if (i !== giusto) {
      el.classList.add('is-salto');
      el.scrollTo({ left: sinistraPer(giusto), behavior: 'instant' });
      setCentro(giusto);
      requestAnimationFrame(() => requestAnimationFrame(() => el.classList.remove('is-salto')));
    }
  }

  function quandoScorre() {
    const el = binario.current;
    if (!el) return;
    setCentro(Math.max(0, Math.min(GIRI * N - 1, indiceAlCentro())));
    // browser senza l'evento di fine scorrimento: il salto aspetta una pausa più lunga
    if (!FINE_SCORRIMENTO) {
      clearTimeout(fermo.current);
      fermo.current = setTimeout(riportaAlMezzo, 350);
    }
  }

  // all'apertura la prima scheda della copia di mezzo sta al centro;
  // il salto tra le copie scatta solo a scorrimento finito (dito sollevato e scatto concluso)
  useLayoutEffect(() => {
    const el = binario.current;
    if (!el) return;
    el.scrollTo({ left: sinistraPer(MEZZO * N), behavior: 'instant' });
    if (FINE_SCORRIMENTO) el.addEventListener('scrollend', riportaAlMezzo);
    return () => {
      clearTimeout(fermo.current);
      el.removeEventListener('scrollend', riportaAlMezzo);
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // i puntini portano alla scheda scelta per la via più corta
  function vaiA(i) {
    const el = binario.current;
    if (!el) return;
    let diff = i - attiva;
    if (diff > N / 2) diff -= N;
    if (diff < -N / 2) diff += N;
    el.scrollTo({ left: sinistraPer(centro + diff), behavior: 'smooth' });
  }

  const tutte = Array.from({ length: GIRI }, (_, g) => schede.map((s, i) => ({ ...s, g, i }))).flat();

  return (
    <div className="screen home">
      <div className="home-testa">
        <h1 className="h1 home-titolo">Il tuo preventivo è davvero equo?</h1>
        <p className="muted home-frase">Confrontalo con i prezzi medi dei ricambi e la tariffa oraria della tua regione.</p>
      </div>

      <section className="carosello" aria-roledescription="carosello" aria-label="Perché scegliere EQUO">
        <div className="carosello-binario" ref={binario} onScroll={quandoScorre} aria-label="Quattro motivi, scorri in un verso o nell'altro">
          {tutte.map((s, k) => (
            <article
              key={`${s.g}-${s.titolo}`}
              className={`carosello-scheda${k === centro ? ' is-attiva' : ''}`}
              aria-roledescription="scheda"
              aria-label={`${s.i + 1} di ${N}`}
              aria-hidden={s.g !== MEZZO ? 'true' : undefined}
            >
              <h2 className="carosello-titolo">{s.titolo}</h2>
              <p className="carosello-testo">{s.testo}</p>
              {/* icona grande in fondo alla scheda verticale */}
              <span className="carosello-icona" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
                  {ICONE[s.icona]}
                </svg>
              </span>
            </article>
          ))}
        </div>
        <div className="carosello-punti">
          {schede.map((s, i) => (
            <button
              key={s.titolo}
              type="button"
              className={`carosello-punto${i === attiva ? ' is-attivo' : ''}`}
              aria-label={`Vai alla scheda ${i + 1} di ${N}`}
              aria-current={i === attiva ? 'true' : undefined}
              onClick={() => vaiA(i)}
            />
          ))}
        </div>
      </section>

      <div className="home-avvio">
        <Button variant="cta" onClick={onStart}>Inizia la verifica</Button>
        <p className="avvio-nota">60 secondi, senza registrazione</p>
      </div>
    </div>
  );
}
