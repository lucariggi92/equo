import { data } from '../../lib/format.js';
import './Footer.css';

// Piede di pagina, solo su schermi larghi (tablet orizzontale e computer): marchio, a cosa serve, data dei prezzi.
// Nessun link: il sito non ha ancora altre pagine.
export default function Footer({ prezziAl }) {
  const anno = new Date().getFullYear();
  return (
    <footer className="piede">
      <div className="piede-dentro">
        <div className="piede-marchio">
          <span className="piede-logo" aria-label="EQUO">E<span className="piede-q">Q</span>UO</span>
          <p className="piede-cosa">Verifica se il preventivo del meccanico è equo.</p>
        </div>
        <ul className="piede-fatti">
          <li>Gratis, senza registrazione</li>
          <li>Nessun dato personale</li>
          {prezziAl && <li>Prezzi dei ricambi aggiornati al {data(prezziAl)}</li>}
        </ul>
        <p className="piede-diritti">© {anno} EQUO</p>
      </div>
    </footer>
  );
}
