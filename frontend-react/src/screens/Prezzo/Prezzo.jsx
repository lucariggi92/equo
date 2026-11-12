import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Keypad from '../../components/Keypad/Keypad.jsx';
import { euro } from '../../lib/format.js';
import '../Welcome/Welcome.css';

export default function Prezzo({ regione, auto, intervento, prezzo, onPrezzo, onNext, onBack }) {
  const addDigit = (d) => onPrezzo((prezzo + d).slice(0, 7));
  return (
    <div className="screen">
      <h1 className="h1">Quanto ti ha chiesto il meccanico?</h1>
      <Card title="Riepilogo">
        <div className="recap">
          <span>Regione: <strong>{regione}</strong></span>
          <span>Veicolo: <strong>{auto?.modello}</strong></span>
          <span>Intervento: <strong>{intervento?.nome}</strong></span>
        </div>
      </Card>
      <div className="amount numeric">{euro(prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.')))} EUR</div>
      <Keypad
        onDigit={addDigit}
        onComma={() => { if (!String(prezzo).includes(',')) onPrezzo(prezzo === '' ? '0,' : prezzo + ','); }}
        onBack={() => onPrezzo(String(prezzo).slice(0, -1))}
      />
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={prezzo === ''} onClick={onNext}>Verifica Preventivo →</Button>
      </div>
    </div>
  );
}
