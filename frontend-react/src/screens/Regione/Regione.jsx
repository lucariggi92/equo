import { useState } from 'react';
import Domanda from '../../components/Domanda/Domanda.jsx';
import Ruota from '../../components/Ruota/Ruota.jsx';
import MappaItalia from '../../components/MappaItalia/MappaItalia.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

// La regione si sceglie facendo girare la ruota (o cercandola): lo schermo resta fermo, scorre solo l'elenco.
// Su computer a destra c'è l'Italia: la regione nella lente si illumina mentre la ruota gira;
// cliccando una regione sulla mappa la ruota gira fino a lei e la sceglie.
export default function Regione({ regioni, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const [evidenziata, setEvidenziata] = useState(value);
  return (
    <Domanda
      titolo="Qual è la tua Regione?"
      spiegazione="Usiamo una tariffa oraria media stimata per ogni regione."
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={!value}
      lato={regioni && <MappaItalia regioni={regioni} evidenziata={evidenziata || value} onScegli={onSelect} />}
    >
      {!regioni && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {regioni && (
        <Ruota
          etichetta="Regione"
          cerca="Cerca regione…"
          opzioni={regioni.map((r) => ({ chiave: String(r.id), etichetta: r.nome, dato: r }))}
          valore={value ? String(value.id) : null}
          onChange={onSelect}
          onCentro={setEvidenziata}
        />
      )}
    </Domanda>
  );
}
