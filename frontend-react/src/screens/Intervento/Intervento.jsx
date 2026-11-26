import { useState } from 'react';
import Domanda from '../../components/Domanda/Domanda.jsx';
import SearchBar from '../../components/SearchBar/SearchBar.jsx';
import ListRow from '../../components/ListRow/ListRow.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';
import './Intervento.css';

// Un solo elenco con pacchetti e pezzi singoli (decisione D1); per la versione scelta non compaiono
// gli interventi non previsti (es. frizione con il cambio automatico). Se manca, si descrive a parole.
// Ogni intervento è una scheda con il pallino di selezione; la ricerca resta fissa in alto, scorre solo l'elenco.
export default function Intervento({ interventi, errore, onRiprova, nonPrevisti = [], value, altro, onSelect, onAltro, onNext, onBack }) {
  const [q, setQ] = useState('');
  const [alfabetico, setAlfabetico] = useState(false);
  const previsti = (interventi || []).filter((i) => !nonPrevisti.includes(i.id));
  const trovati = previsti.filter((i) => `${i.nome} ${i.descrizione || ''}`.toLowerCase().includes(q.toLowerCase()));
  const list = alfabetico ? [...trovati].sort((a, b) => a.nome.localeCompare(b.nome, 'it')) : trovati;
  const altroValido = altro.trim().length >= 3;
  return (
    <Domanda
      titolo="Che intervento devi fare?"
      fisso={<SearchBar value={q} onChange={setQ} placeholder="Cerca intervento…" />}
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={!value && !altroValido}
    >
      {!interventi && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {interventi && (
        <div className="interventi-testa">
          <span className="interventi-conta">
            {q.trim() ? `Trovati (${list.length})` : `Interventi (${list.length})`}
          </span>
          <button
            type="button"
            className={`interventi-ordine${alfabetico ? ' is-attivo' : ''}`}
            aria-pressed={alfabetico}
            onClick={() => setAlfabetico((a) => !a)}
          >
            A-Z
          </button>
        </div>
      )}
      {list.length > 0 && (
        <div className="lista-schede">
          {list.map((i) => (
            <ListRow
              key={i.id}
              scheda
              label={i.nome}
              sub={i.descrizione}
              selected={value?.id === i.id}
              onClick={() => { onSelect(i); onAltro(''); }}
            />
          ))}
        </div>
      )}
      {interventi && q.trim() && list.length === 0 && <p className="muted interventi-nessuno">Nessun intervento per "{q.trim()}".</p>}

      {/* non è in elenco: lo si descrive a parole */}
      <section className="interventi-aiuto" aria-labelledby="interventi-aiuto-titolo">
        <div className="interventi-aiuto-testa">
          <span className="interventi-aiuto-icona" aria-hidden="true">?</span>
          <div>
            <h2 id="interventi-aiuto-titolo" className="interventi-aiuto-titolo">Non trovi l'intervento?</h2>
            <p className="interventi-aiuto-testo">Descrivilo con parole tue: registriamo la richiesta.</p>
          </div>
        </div>
        <textarea
          className="note-input"
          value={altro}
          maxLength={120}
          onChange={(e) => { onAltro(e.target.value); if (e.target.value) onSelect(null); }}
          placeholder="Es. sostituzione ammortizzatori"
          aria-label="Descrivi l'intervento se non è in elenco"
        />
        {altro && !altroValido && <p className="muted">Scrivi almeno 3 caratteri.</p>}
      </section>
    </Domanda>
  );
}
