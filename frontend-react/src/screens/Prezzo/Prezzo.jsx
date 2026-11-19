import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Keypad from '../../components/Keypad/Keypad.jsx';
import { euro, nomeAuto } from '../../lib/format.js';
import '../Welcome/Welcome.css';

export default function Prezzo({ regione, auto, intervento, altro, qualita, officina, prezzo, ivaInclusa, errore, onPrezzo, onIva, onNext, onBack }) {
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
          <span>Veicolo: <strong>{nomeAuto(auto)}</strong> {auto?.variante?.etichetta}{auto?.anno ? ` (${auto.anno})` : ''}</span>
          <span>Intervento: <strong>{intervento?.nome || altro}</strong></span>
          <span>Ricambi: <strong>{qualita?.etichetta}</strong> · Officina: <strong>{officina?.etichetta}</strong></span>
        </div>
      </Card>
      <div className="amount numeric">{euro(prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.')))} EUR</div>
      <Keypad
        onDigit={addDigit}
        onComma={() => { if (!String(prezzo).includes(',')) onPrezzo(prezzo === '' ? '0,' : prezzo + ','); }}
        onBack={() => onPrezzo(String(prezzo).slice(0, -1))}
      />
      <span className="msg-label">L'importo comprende l'IVA?</span>
      <div className="stats">
        <Button variant={ivaInclusa === true ? 'primary' : 'secondary'} onClick={() => onIva(true)}>Sì, IVA inclusa</Button>
        <Button variant={ivaInclusa === false ? 'primary' : 'secondary'} onClick={() => onIva(false)}>No, IVA esclusa</Button>
      </div>
      {errore && <Card><p className="muted">{errore}</p></Card>}
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={prezzo === '' || ivaInclusa === null} onClick={onNext}>Verifica Preventivo →</Button>
      </div>
    </div>
  );
}
