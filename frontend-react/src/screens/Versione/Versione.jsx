import Domanda from '../../components/Domanda/Domanda.jsx';
import Schede from '../../components/Schede/Schede.jsx';
import { fotoModello } from '../../data/fotoModelli.js';
import { DIFFERENZE } from '../../data/restyling.js';
import { nomeComune, nomeVisibile } from '../../lib/versioni.js';
import '../Welcome/Welcome.css';

// Compare solo quando l'anno non basta a capire la versione (anno di passaggio o "non lo so"):
// le versioni possibili a schede, con la foto del dettaglio che le distingue (per la 500: i fanali posteriori).
export default function Versione({ gruppo, candidate, value, onSelect, onNext, onBack }) {
  const primo = candidate[0] || gruppo?.versioni[0];
  const diff = primo ? DIFFERENZE[`${primo.marca.nome} ${primo.modello.nome}`] : null;
  const opzioni = candidate.map((a) => {
    const v = a.variante;
    const fine = v.anno_produzione_fine ?? 'oggi';
    const d = diff?.versioni?.[v.anno_produzione_inizio];
    return {
      chiave: String(v.id),
      titolo: d?.titolo || `${nomeVisibile(a)} ${v.anno_produzione_inizio}-${fine}`,
      sotto: d?.sotto || v.etichetta,
      foto: d?.foto || fotoModello(a),
      dato: a,
    };
  });
  return (
    <Domanda
      titolo={diff?.domanda || `Quale ${nomeComune(primo?.modello?.nome)} hai?`}
      spiegazione={diff?.suggerimento || "Dall'anno non riusciamo a capire la versione esatta: scegli quella che somiglia alla tua."}
      onBack={onBack}
      onNext={onNext}
      avantiDisabilitato={!value || !candidate.some((a) => a.variante.id === value.variante?.id)}
    >
      <Schede etichetta="Versione" opzioni={opzioni} valore={value?.variante ? String(value.variante.id) : null} onChange={onSelect} />
    </Domanda>
  );
}
