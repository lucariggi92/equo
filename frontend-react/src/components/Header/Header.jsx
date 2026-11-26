import { useState } from 'react';
import './Header.css';

// versione attiva: quella scelta col pulsante (salvata nel browser), altrimenti quella del telefono
function temaAttuale() {
  const scelto = document.documentElement.dataset.theme;
  if (scelto === 'light' || scelto === 'dark') return scelto;
  return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

function applicaTema(tema) {
  document.documentElement.dataset.theme = tema;
  try { localStorage.setItem('equo-tema', tema); } catch { /* navigazione privata: vale solo per questa visita */ }
}

// onBack: se presente, mostra la freccia per tornare indietro in alto a sinistra (comoda col pollice sul telefono)
export default function Header({ title = 'EQUO', right, onBack }) {
  const [tema, setTema] = useState(temaAttuale);
  const scuro = tema === 'dark';

  function cambiaTema() {
    const nuovo = scuro ? 'light' : 'dark';
    applicaTema(nuovo);
    setTema(nuovo);
  }

  return (
    <header className="app-header">
      <span className="app-header-left">
        {onBack && (
          <button type="button" className="app-back" onClick={onBack} aria-label="Torna alla domanda precedente">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M19 12H5M11 18l-6-6 6-6" />
            </svg>
          </button>
        )}
        {/* EQUO con la Q verde; i lettori di schermo leggono la parola intera */}
        <span className="app-logo" aria-label={title}>
          {title === 'EQUO' ? <>E<span className="app-logo-q">Q</span>UO</> : title}
        </span>
      </span>
      <span className="app-header-right">
        {right}
        <button
          type="button"
          className="app-tema"
          onClick={cambiaTema}
          aria-label={scuro ? 'Passa alla versione chiara' : 'Passa alla versione scura'}
        >
          {scuro ? (
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="4" />
              <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
            </svg>
          ) : (
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z" />
            </svg>
          )}
        </button>
      </span>
    </header>
  );
}
