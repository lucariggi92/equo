import { useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

const ANNO_MAX = new Date().getFullYear() + 1;
const annoValido = (a) => /^\d{4}$/.test(a) && Number(a) >= 1980 && Number(a) <= ANNO_MAX;
const inProduzione = (v, anno) => anno === null
  || (v.anno_produzione_inizio <= anno && (v.anno_produzione_fine === null || v.anno_produzione_fine >= anno));

function dettagliVersione(v) {
  // alcune versioni hanno già gli anni nel nome (es. 500 "(2007-2015)"): non si ripetono
  const anni = v.etichetta.includes(String(v.anno_produzione_inizio)) ? null
    : v.anno_produzione_fine ? `${v.anno_produzione_inizio}-${v.anno_produzione_fine}` : `dal ${v.anno_produzione_inizio}`;
  return [v.etichetta, v.cambio && v.cambio !== 'manuale' && `cambio ${v.cambio}`, anni].filter(Boolean).join(' · ');
}

// Come nella V1: un solo elenco, modello sopra e motore sotto. Ogni riga è una versione dell'auto.
// L'anno di immatricolazione è facoltativo: nasconde le versioni non prodotte in quell'anno e va alla verifica.
export default function Auto({ versioni, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const [anno, setAnno] = useState(value?.anno ? String(value.anno) : '');
  const annoNum = annoValido(anno) ? Number(anno) : null;

  const list = (versioni || [])
    .filter((a) => inProduzione(a.variante, annoNum))
    .filter((a) => `${a.marca.nome} ${a.modello.nome} ${a.variante.etichetta}`.toLowerCase().includes(q.toLowerCase()));

  function cambiaAnno(testo) {
    const a = testo.replace(/\D/g, '').slice(0, 4);
    setAnno(a);
    if (!value) return;
    const nuovo = annoValido(a) ? Number(a) : null;
    onSelect(inProduzione(value.variante, nuovo) ? { ...value, anno: nuovo } : null);
  }

  return (
    <div className="screen">
      <h1 className="h1">Che auto guidi?</h1>
      <SearchBar value={q} onChange={setQ} placeholder="Cerca modello..." />
      {!versioni && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {list.length > 0 && (
        <div className="list-wrap">
          {list.map((a) => (
            <ListRow
              key={a.variante.id}
              label={`${a.marca.nome} ${a.modello.nome}`}
              sub={dettagliVersione(a.variante)}
              selected={value?.variante?.id === a.variante.id}
              onClick={() => onSelect({ ...a, anno: annoNum })}
            />
          ))}
        </div>
      )}
      {versioni && list.length === 0 && annoNum !== null && (
        <Card><p className="muted">Nessuna di queste auto era in produzione nel {annoNum}: controlla l'anno o toglilo.</p></Card>
      )}
      <input
        className="search"
        inputMode="numeric"
        value={anno}
        onChange={(e) => cambiaAnno(e.target.value)}
        placeholder="Anno di immatricolazione (facoltativo)"
        aria-label="Anno di immatricolazione (facoltativo)"
      />
      {anno.length === 4 && !annoValido(anno) && <p className="muted">L'anno deve essere tra 1980 e {ANNO_MAX}.</p>}
      <Card><p className="muted">Non trovi il modello? Seleziona quello più simile per segmento.</p></Card>
      <div className="stats">
        <Button variant="secondary" onClick={onBack}>Indietro</Button>
        <Button disabled={!value?.variante} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
