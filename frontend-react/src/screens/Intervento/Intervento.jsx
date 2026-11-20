import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

// Un solo elenco con pacchetti e pezzi singoli (decisione D1); per la versione scelta non compaiono
// gli interventi non previsti (es. frizione con il cambio automatico). Se manca, si descrive a parole.
export default function Intervento({ interventi, errore, onRiprova, nonPrevisti = [], value, altro, onSelect, onAltro, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = (interventi || [])
    .filter((i) => !nonPrevisti.includes(i.id))
    .filter((i) => `${i.nome} ${i.descrizione || ''}`.toLowerCase().includes(q.toLowerCase()));
  const altroValido = altro.trim().length >= 3;
  return (
    <div className="screen">
      <h1 className="h1">Che intervento devi fare?</h1>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca intervento..." />
      {!interventi && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      <div className="list-wrap">
        {list.map((i) => (
          <ListRow
            key={i.id}
            label={i.nome}
            sub={i.descrizione}
            selected={value?.id === i.id}
            onClick={() => { onSelect(i); onAltro(''); }}
          />
        ))}
      </div>
      <textarea
        className="note-input"
        value={altro}
        maxLength={120}
        onChange={(e) => { onAltro(e.target.value); if (e.target.value) onSelect(null); }}
        placeholder="Non trovi l'intervento? Descrivilo qui (es. sostituzione ammortizzatori)"
        aria-label="Descrivi l'intervento se non è in elenco"
      />
      {altro && !altroValido && <p className="muted">Scrivi almeno 3 caratteri.</p>}
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value && !altroValido} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
