import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import { REGIONI } from '../../data/regioni.js';
import '../Welcome/Welcome.css';

export default function Regione({ value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = REGIONI.filter((r) => r.toLowerCase().includes(q.toLowerCase()));
  return (
    <div className="screen">
      <h1 className="h1">Qual è la tua Regione?</h1>
      <p className="muted">Usiamo le tariffe orarie medie da dati camerali/artigianato.</p>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca regione..." />
      <div className="list-wrap">
        {list.map((r) => (
          <ListRow
            key={r}
            label={r}
            selected={value === r}
            onClick={() => onSelect(r)}
          />
        ))}
      </div>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
