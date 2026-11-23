import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Keypad from '../../components/Keypad/Keypad.jsx';
import { euro, nomeAuto } from '../../lib/format.js';
import '../Welcome/Welcome.css';

// Solo l'importo: la domanda sull'IVA è la schermata successiva.
export default function Prezzo({ regione, auto, intervento, altro, prezzo, onPrezzo, onNext, onBack }) {
  const importo = prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.'));
  // al massimo 2 decimali, come accetta l'API
  const addDigit = (d) => {
    if ((String(prezzo).split(',')[1] ?? '').length >= 2) return;
    onPrezzo((prezzo + d).slice(0, 7));
  };
  return (
    <div className="screen">
      <h1 className="h1">Quanto ti ha chiesto il meccanico?</h1>
      <Card title="Riepilogo">
        <div className="recap">
          <span>Regione: <strong>{regione?.nome}</strong></span>
          <span>Veicolo: <strong>{nomeAuto(auto)}</strong></span>
          <span>Intervento: <strong>{intervento?.nome || altro}</strong></span>
        </div>
      </Card>
      <div className="amount numeric">{euro(importo)} EUR</div>
      <Keypad
        onDigit={addDigit}
        onComma={() => { if (!String(prezzo).includes(',')) onPrezzo(prezzo === '' ? '0,' : prezzo + ','); }}
        onBack={() => onPrezzo(String(prezzo).slice(0, -1))}
      />
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!(importo > 0)} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
