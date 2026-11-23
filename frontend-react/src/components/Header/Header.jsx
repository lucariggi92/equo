import './Header.css';

// onBack: se presente, mostra la freccia per tornare indietro in alto a sinistra (comoda col pollice sul telefono)
export default function Header({ title = 'EQUO', right, onBack }) {
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
        <span className="app-logo">{title}</span>
      </span>
      {right && <span className="app-header-right">{right}</span>}
    </header>
  );
}
