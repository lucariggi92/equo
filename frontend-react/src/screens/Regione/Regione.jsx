import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

export default function Regione({ regioni, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = (regioni || []).filter((r) => r.nome.toLowerCase().includes(q.toLowerCase()));
  return (
    <div className="screen">
      <h1 className="h1">Qual è la tua Regione?</h1>
      <p className="muted">Usiamo una tariffa oraria media stimata per ogni regione.</p>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca regione..." />
      {!regioni && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      <div className="list-wrap">
        {list.map((r) => (
          <ListRow
            key={r.id}
            label={r.nome}
            selected={value?.id === r.id}
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
