import Domanda from '../../components/Domanda/Domanda.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Card from '../../components/Card/Card.jsx';
import '../Welcome/Welcome.css';

// Una domanda, una scelta da elenco: usata per qualità dei ricambi, tipo di officina e IVA.
// Stesso aspetto della domanda sull'intervento: schede separate con il pallino di selezione.
export default function SceltaSingola({ titolo, opzioni, value, errore, testoAvanti = 'Avanti', onSelect, onNext, onBack }) {
  return (
    <Domanda
      titolo={titolo}
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={value === null || value === undefined}
      testoAvanti={testoAvanti}
    >
      <div className="lista-schede">
        {(opzioni || []).map((x) => (
          <ListRow key={String(x.valore)} scheda label={x.etichetta} sub={x.sotto} selected={value === x.valore} onClick={() => onSelect(x.valore)} />
        ))}
      </div>
      {errore && <Card><p className="muted">{errore}</p></Card>}
    </Domanda>
  );
}
