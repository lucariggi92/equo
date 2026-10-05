import './Header.css';

export default function Header({ title = 'EQUO', right }) {
  return (
    <header className="app-header">
      <span className="app-logo">{title}</span>
      {right && <span className="app-header-right">{right}</span>}
    </header>
  );
}
