import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Badge from '../../components/Badge/Badge.jsx';
import { data } from '../../lib/format.js';
import './Welcome.css';

export default function Welcome({ prezziAl, onStart }) {
  return (
    <div className="screen">
      <Badge tone="info">Zero registrazioni • Gratuito • 60 secondi</Badge>
      <h1 className="h1">Il tuo preventivo auto è davvero equo?</h1>
      <p className="muted">Confronta all'istante i costi di ricambi e manodopera con i prezzi medi dei ricambi e le tariffe orarie della tua regione.</p>
      <Card title="La nostra mission">
        <p className="mission">Riequilibriamo la trasparenza tra officina e automobilista. Solo numeri chiari, nessun dato personale.</p>
        <ul className="points">
          <li>Tariffe orarie stimate per ogni regione</li>
          <li>Prezzi medi ricambi OEM e compatibili</li>
          <li>Bozza messaggio cordiale per il meccanico</li>
        </ul>
      </Card>
      <Card>
        <p className="muted"><strong className="white">100% Anonimo.</strong> Nessun PDF, nessuna targa, nessun GPS.</p>
      </Card>
      <div className="stats">
        <div className="stat"><span className="stat-label">Tempo medio</span><span className="stat-value">60 sec</span></div>
        <div className="stat"><span className="stat-label">Prezzi aggiornati al</span><span className="stat-value mint">{prezziAl ? data(prezziAl) : '—'}</span></div>
      </div>
      <Button onClick={onStart}>Inizia la Verifica (60 sec)</Button>
    </div>
  );
}
