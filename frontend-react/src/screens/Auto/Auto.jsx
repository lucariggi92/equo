import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import { AUTO } from '../../data/auto.js';
import '../Welcome/Welcome.css';

export default function Auto({ value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = AUTO.filter((a) => a.modello.toLowerCase().includes(q.toLowerCase()));
  return (
    <div className="screen">
      <h1 className="h1">Che auto guidi?</h1>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca modello..." />
      <div className="list-wrap">
        {list.map((a) => (
          <ListRow
            key={a.modello}
            label={a.modello}
            sub={a.motore}
            selected={value?.modello === a.modello}
            onClick={() => onSelect(a)}
          />
        ))}
      </div>
      <Card><p className="muted">Non trovi il modello? Seleziona quello più simile per segmento.</p></Card>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
