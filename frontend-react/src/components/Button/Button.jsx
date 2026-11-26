import './Button.css';

// variant: primary, secondary, oppure cta (pillola verde larga, per l'azione principale di una schermata)
export default function Button({ children, variant = 'primary', disabled, onClick, type = 'button' }) {
  return (
    <button type={type} disabled={disabled} onClick={onClick} className={`btn btn-${variant}`}>
      {children}
    </button>
  );
}
