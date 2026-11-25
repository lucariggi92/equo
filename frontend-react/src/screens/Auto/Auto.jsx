import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

function dettagliVersione(v) {
  // alcune versioni hanno già gli anni nel nome (es. 500 "(2007-2015)"): non si ripetono
  const anni = v.etichetta.includes(String(v.anno_produzione_inizio)) ? null
    : v.anno_produzione_fine ? `${v.anno_produzione_inizio}-${v.anno_produzione_fine}` : `dal ${v.anno_produzione_inizio}`;
  return [v.etichetta, v.cambio && v.cambio !== 'manuale' && `cambio ${v.cambio}`, anni].filter(Boolean).join(' · ');
}

// Come nella V1: un solo elenco, modello sopra e motore sotto. Ogni riga è una versione dell'auto.
// L'anno di immatricolazione è la domanda successiva (schermata Anno).
export default function Auto({ versioni, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const list = (versioni || [])
    .filter((a) => `${a.marca.nome} ${a.modello.nome} ${a.variante.etichetta}`.toLowerCase().includes(q.toLowerCase()));

  return (
    <div className="screen">
      <h1 className="h1">Che auto guidi?</h1>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca modello…" />
      {!versioni && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {list.length > 0 && (
        <div className="list-wrap">
          {list.map((a) => (
            <ListRow
              key={a.variante.id}
              label={`${a.marca.nome} ${a.modello.nome}`}
              sub={dettagliVersione(a.variante)}
              selected={value?.variante?.id === a.variante.id}
              onClick={() => onSelect(a)}
            />
          ))}
        </div>
      )}
      <Card><p className="muted">Non trovi il modello? Seleziona quello più simile per segmento.</p></Card>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value?.variante} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
