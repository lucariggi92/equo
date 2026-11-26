import Domanda from '../../components/Domanda/Domanda.jsx';
import Ruota from '../../components/Ruota/Ruota.jsx';
import StatoCaricamento from '../../components/StatoCaricamento/StatoCaricamento.jsx';
import '../Welcome/Welcome.css';

// Prima la marca, con la ruota; il modello esatto è la domanda successiva.
// Le marche sono quelle presenti nel database, in ordine alfabetico.
export default function Marca({ marche, errore, onRiprova, value, onSelect, onNext, onBack }) {
  const ordinate = marche ? [...marche].sort((a, b) => a.nome.localeCompare(b.nome, 'it')) : null;
  return (
    <Domanda
      titolo="Di che marca è la tua auto?"
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={!value}
    >
      {!ordinate && <StatoCaricamento errore={errore} onRiprova={onRiprova} />}
      {ordinate && (
        <Ruota
          etichetta="Marca"
          cerca="Cerca marca…"
          opzioni={ordinate.map((m) => ({ chiave: m.nome, etichetta: m.nome, dato: m }))}
          valore={value ? value.nome : null}
          onChange={onSelect}
        />
      )}
    </Domanda>
  );
}
