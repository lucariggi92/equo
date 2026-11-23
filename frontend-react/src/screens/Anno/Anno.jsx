import Button from '../../components/Button/Button.jsx';
import { anniDiProduzione, nomeAuto } from '../../lib/format.js';
import '../Welcome/Welcome.css';
import './Anno.css';

// Anno di immatricolazione, facoltativo, scelto da un menù a tendina.
export default function Anno({ auto, value, onChange, onNext, onBack }) {
  const anni = anniDiProduzione(auto?.variante);
  return (
    <div className="screen">
      <h1 className="h1">In che anno è stata immatricolata?</h1>
      <p className="muted">
        Facoltativo. Ti proponiamo gli anni in cui la {nomeAuto(auto)} {auto?.variante?.etichetta} è stata prodotta
        ({anni[anni.length - 1]}–{anni[0]}).
      </p>
      <select className="search anno-select" value={value} onChange={(e) => onChange(e.target.value)} aria-label="Anno di immatricolazione">
        <option value="">Non lo so / preferisco non dirlo</option>
        {anni.map((a) => <option key={a} value={a}>{a}</option>)}
      </select>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
