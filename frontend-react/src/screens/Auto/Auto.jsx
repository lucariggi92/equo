import { useEffect, useState } from 'react';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import { varianti } from '../../api/client.js';
import '../Welcome/Welcome.css';

const ANNO_MAX = new Date().getFullYear() + 1;
const annoValido = (a) => /^\d{4}$/.test(a) && Number(a) >= 1980 && Number(a) <= ANNO_MAX;

function dettagliVersione(v) {
  const anni = v.anno_produzione_fine ? `${v.anno_produzione_inizio}-${v.anno_produzione_fine}` : `dal ${v.anno_produzione_inizio}`;
  return [v.alimentazione, v.cambio && `cambio ${v.cambio}`, anni].filter(Boolean).join(' · ');
}

// Scelta dell'auto in tre passi: marca -> modello -> versione (con l'anno facoltativo, che filtra le versioni).
export default function Auto({ marche, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const [marca, setMarca] = useState(value?.marca || null);
  const [modello, setModello] = useState(value?.modello || null);
  const [anno, setAnno] = useState(value?.anno ? String(value.anno) : '');
  const [versioni, setVersioni] = useState(null);
  const [erroreVersioni, setErroreVersioni] = useState(null);
  const [tentativo, setTentativo] = useState(0);
  const [q, setQ] = useState('');

  const annoFiltro = annoValido(anno) ? Number(anno) : null;

  useEffect(() => {
    if (!modello) return undefined;
    let attivo = true;
    varianti(modello.id, annoFiltro)
      .then((r) => attivo && setVersioni(r.varianti))
      .catch((e) => attivo && setErroreVersioni(e.message));
    return () => { attivo = false; };
  }, [modello, annoFiltro, tentativo]);

  function scegliMarca(m) {
    setMarca(m);
    setQ('');
  }
  function scegliModello(m) {
    setVersioni(null);
    setErroreVersioni(null);
    setModello(m);
    setQ('');
  }
  function cambiaAnno(testo) {
    const a = testo.replace(/\D/g, '').slice(0, 4);
    setAnno(a);
    if (annoValido(a) || a === '') setVersioni(null);
    onSelect(null);
  }
  function indietro() {
    setQ('');
    if (modello) { setModello(null); setVersioni(null); setAnno(''); onSelect(null); return; }
    if (marca) { setMarca(null); return; }
    onBack();
  }

  const filtra = (testo) => testo.toLowerCase().includes(q.toLowerCase());
  let titolo = 'Che auto guidi?';
  let righe = null;
  if (!marca) {
    righe = marche && marche.filter((m) => filtra(m.nome)).map((m) => (
      <ListRow key={m.id} label={m.nome} sub={`${m.modelli.length} ${m.modelli.length === 1 ? 'modello' : 'modelli'}`} onClick={() => scegliMarca(m)} />
    ));
  } else if (!modello) {
    titolo = `Quale ${marca.nome}?`;
    righe = marca.modelli.filter((m) => filtra(m.nome)).map((m) => (
      <ListRow key={m.id} label={m.nome} onClick={() => scegliModello(m)} />
    ));
  } else {
    titolo = 'Quale versione?';
    righe = versioni && versioni.filter((v) => filtra(v.etichetta)).map((v) => (
      <ListRow
        key={v.id}
        label={v.etichetta}
        sub={dettagliVersione(v)}
        selected={value?.variante?.id === v.id}
        onClick={() => onSelect({ marca, modello, variante: v, anno: annoFiltro })}
      />
    ));
  }

  return (
    <div className="screen">
      <h1 className="h1">{titolo}</h1>
      {marca && <p className="muted">{marca.nome}{modello ? ` › ${modello.nome}` : ''}</p>}
      {modello && (
        <input
          className="search"
          inputMode="numeric"
          value={anno}
          onChange={(e) => cambiaAnno(e.target.value)}
          placeholder="Anno di immatricolazione (facoltativo)"
          aria-label="Anno di immatricolazione (facoltativo)"
        />
      )}
      {anno !== '' && !annoValido(anno) && anno.length === 4 && (
        <p className="muted">L'anno deve essere tra 1980 e {ANNO_MAX}.</p>
      )}
      <SearchBar value={q} onChange={setQ} placeholder={!marca ? 'Cerca marca...' : !modello ? 'Cerca modello...' : 'Cerca versione...'} />
      {!marca && !marche && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {modello && !versioni && (
        <StatoCaricamento errore={erroreVersioni} onRiprova={() => { setErroreVersioni(null); setTentativo((t) => t + 1); }} />
      )}
      {righe && righe.length > 0 && <div className="list-wrap">{righe}</div>}
      {modello && versioni && versioni.length === 0 && (
        <Card><p className="muted">Nessuna versione di questo modello era in produzione in quell'anno: controlla l'anno o toglilo.</p></Card>
      )}
      <Card><p className="muted">Non trovi il modello? Seleziona quello più simile per segmento.</p></Card>
      <div className="stats">
        <Button variant="secondary" onClick={indietro}>Indietro</Button>
        <Button disabled={!value?.variante} onClick={onNext}>Avanti</Button>
      </div>
    </div>
  );
}
