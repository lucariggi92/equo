import './Button.css';

// variant: primary, secondary, oppure cta (pillola con freccia, per l'azione principale di una schermata)
export default function Button({ children, variant = 'primary', disabled, onClick, type = 'button' }) {
  return (
    <button type={type} disabled={disabled} onClick={onClick} className={`btn btn-${variant}`}>
      {variant === 'cta' ? <span className="btn-cta-testo">{children}</span> : children}
      {variant === 'cta' && (
        <span className="btn-cta-freccia" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </span>
      )}
    </button>
  );
}
