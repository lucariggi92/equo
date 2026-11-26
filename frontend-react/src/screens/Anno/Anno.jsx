import Domanda from '../../components/Domanda/Domanda.jsx';
import { anniDelGruppo, motoreBase, nomeVisibile } from '../../lib/versioni.js';
import '../../components/SearchBar/SearchBar.css';
import '../Welcome/Welcome.css';
import './Anno.css';

// Anno di immatricolazione, facoltativo, da una tendina classica con gli anni di produzione del modello scelto.
// Serve anche a scegliere la versione esatta quando il modello ha avuto un restyling.
export default function Anno({ gruppo, value, onChange, onNext, onBack }) {
  const anni = anniDelGruppo(gruppo);
  const primo = gruppo?.versioni[0];
  return (
    <Domanda
      titolo="In che anno è stata immatricolata?"
      spiegazione={`Facoltativo. Ti proponiamo gli anni in cui la ${nomeVisibile(primo)} ${motoreBase(primo?.variante)} è stata prodotta (${anni[anni.length - 1]}–${anni[0]}).`}
      onBack={onBack}
      onNext={onNext}
    >
      <label className="anno-campo">
        <span className="anno-etichetta">Anno di immatricolazione</span>
        <select className="search anno-select" value={value} onChange={(e) => onChange(e.target.value)}>
          <option value="">Non lo so / preferisco non dirlo</option>
          {anni.map((a) => <option key={a} value={a}>{a}</option>)}
        </select>
      </label>
    </Domanda>
  );
}
