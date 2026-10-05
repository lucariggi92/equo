import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import { INTERVENTI } from '../../data/interventi.js';
import '../Welcome/Welcome.css';

export default function Intervento({ value, note, onSelect, onNote, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = INTERVENTI.filter((i) => i.nome.toLowerCase().includes(q.toLowerCase()));
  return (
    <div className="screen">
      <h1 className="h1">Che intervento devi fare?</h1>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca intervento..." />
      <div className="list-wrap">
        {list.map((i) => (
          <ListRow
            key={i.nome}
            label={i.nome}
            sub={i.desc}
            selected={value?.nome === i.nome}
            onClick={() => onSelect(i)}
          />
        ))}
      </div>
      <textarea
        className="note-input"
        value={note}
        maxLength={280}
        onChange={(e) => onNote(e.target.value)}
        placeholder="Note libere dal preventivo (opzionale)"
      />
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
