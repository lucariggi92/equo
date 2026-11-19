import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import '../Welcome/Welcome.css';

// Qualità del ricambio e tipo di officina: cambiano prezzi dei ricambi, ricarico e tariffa oraria.
export default function Scelte({ qualita, tipiOfficina, valueQualita, valueOfficina, onQualita, onOfficina, onNext, onBack }) {
  return (
    <div className="screen">
      <h1 className="h1">Ricambi e officina</h1>
      <p className="muted">Se non sai la qualità dei ricambi, chiedila all'officina: è scritta sul preventivo o in fattura.</p>
      <span className="msg-label">Qualità del ricambio</span>
      <div className="list-wrap">
        {(qualita || []).map((x) => (
          <ListRow key={x.valore} label={x.etichetta} selected={valueQualita === x.valore} onClick={() => onQualita(x.valore)} />
        ))}
      </div>
      <span className="msg-label">Dove fai l'intervento</span>
      <div className="list-wrap">
        {(tipiOfficina || []).map((x) => (
          <ListRow key={x.valore} label={x.etichetta} selected={valueOfficina === x.valore} onClick={() => onOfficina(x.valore)} />
        ))}
      </div>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!valueQualita || !valueOfficina} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
