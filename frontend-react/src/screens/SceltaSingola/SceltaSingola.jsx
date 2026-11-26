import Domanda from '../../components/Domanda/Domanda.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Card from '../../components/Card/Card.jsx';
import '../Welcome/Welcome.css';

// Una domanda, una scelta da elenco: usata per qualità dei ricambi, tipo di officina e IVA.
export default function SceltaSingola({ titolo, spiegazione, opzioni, value, errore, testoAvanti = 'Avanti', onSelect, onNext, onBack }) {
  return (
    <Domanda
      titolo={titolo}
      spiegazione={spiegazione}
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={value === null || value === undefined}
      testoAvanti={testoAvanti}
    >
      <div className="list-wrap">
        {(opzioni || []).map((x) => (
          <ListRow key={String(x.valore)} label={x.etichetta} sub={x.sotto} selected={value === x.valore} onClick={() => onSelect(x.valore)} />
        ))}
      </div>
      {errore && <Card><p className="muted">{errore}</p></Card>}
    </Domanda>
  );
}
