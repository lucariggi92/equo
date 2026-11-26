import { useState } from 'react';
import Domanda from '../../components/Domanda/Domanda.jsx';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import Schede from '../../components/Schede/Schede.jsx';
import { fotoModello } from '../../data/fotoModelli.js';
import { motoreBase, nomeVisibile, raggruppaVersioni } from '../../lib/versioni.js';
import '../Welcome/Welcome.css';

// sotto il nome: motore, cambio se non manuale, anni di produzione di tutto il gruppo
function dettagli(g) {
  const v = g.versioni[0].variante;
  const anni = g.inCorso ? `dal ${g.inizio}` : `${g.inizio}-${g.fine}`;
  return [motoreBase(v), v.cambio && v.cambio !== 'manuale' && `cambio ${v.cambio}`, anni].filter(Boolean).join(', ');
}

const semplice = (t) => String(t ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

// Il modello della marca scelta, a schede con la foto: aiuta chi riconosce l'auto a vista ma non ne sa il nome.
// Le versioni che differiscono solo per il periodo (prima e dopo un restyling) sono una sola scheda:
// la versione esatta la decide poi l'anno di immatricolazione.
export default function Auto({ marca, versioni, value, onSelect, onNext, onBack }) {
  const [q, setQ] = useState('');
  const opzioni = raggruppaVersioni(versioni).map((g) => ({
    chiave: g.chiave,
    titolo: nomeVisibile(g.versioni[0]),
    sotto: dettagli(g),
    foto: fotoModello(g.versioni[0]),
    dato: g,
  }));
  const visibili = q.trim()
    ? opzioni.filter((o) => semplice(`${o.titolo} ${o.sotto} ${o.dato.modello.nome}`).includes(semplice(q.trim())))
    : opzioni;
  return (
    <Domanda
      titolo={`Quale ${marca?.nome || 'auto'} guidi?`}
      fisso={<SearchBar value={q} onChange={setQ} placeholder="Cerca modello o motore…" />}
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={!value}
    >
      {visibili.length > 0
        ? <Schede etichetta={`Modello ${marca?.nome || ''}`} opzioni={visibili} valore={value?.chiave ?? null} onChange={onSelect} />
        : <p className="muted schede-nessuno">Nessun modello per "{q.trim()}".</p>}
    </Domanda>
  );
}
