import './Button.css';

export default function Button({ children, variant = 'primary', disabled, onClick, type = 'button' }) {
  return (
    <button type={type} disabled={disabled} onClick={onClick} className={`btn btn-${variant}`}>
      {children}
    </button>
  );
}
