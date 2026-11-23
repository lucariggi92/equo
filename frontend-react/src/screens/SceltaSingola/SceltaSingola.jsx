import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import '../Welcome/Welcome.css';

// Una domanda, una scelta da elenco: usata per qualità dei ricambi, tipo di officina e IVA.
export default function SceltaSingola({ titolo, spiegazione, opzioni, value, errore, testoAvanti = 'Avanti', onSelect, onNext, onBack }) {
  return (
    <div className="screen">
      <h1 className="h1">{titolo}</h1>
      {spiegazione && <p className="muted">{spiegazione}</p>}
      <div className="list-wrap">
        {(opzioni || []).map((x) => (
          <ListRow key={String(x.valore)} label={x.etichetta} sub={x.sotto} selected={value === x.valore} onClick={() => onSelect(x.valore)} />
        ))}
      </div>
      {errore && <Card><p className="muted">{errore}</p></Card>}
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={value === null || value === undefined} onClick={onNext}>{testoAvanti}</Button>
      </div>
    </div>
  );
}
