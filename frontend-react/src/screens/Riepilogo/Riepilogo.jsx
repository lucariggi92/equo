import Domanda from '../../components/Domanda/Domanda.jsx';
import Card from '../../components/Card/Card.jsx';
import { euro } from '../../lib/format.js';
import { motoreBase, nomeVisibile } from '../../lib/versioni.js';
import '../Welcome/Welcome.css';
import '../Report/Report.css';
import './Riepilogo.css';

// Pagina a parte, dopo l'ultima domanda: tutte le risposte da controllare, poi si avvia la verifica.
// Per cambiare una risposta si torna indietro con la freccia o col gesto del telefono.
export default function Riepilogo({ regione, auto, anno, intervento, altro, qualita, officina, prezzo, ivaInclusa, opzioni, errore, onNext, onBack }) {
  const importo = prezzo === '' ? 0 : Number(String(prezzo).replace(',', '.'));
  const nomeQualita = opzioni?.qualita?.find((q) => q.valore === qualita)?.etichetta;
  const nomeOfficina = opzioni?.tipi_officina?.find((o) => o.valore === officina)?.etichetta;
  const voci = [
    ['Regione', regione?.nome],
    ['Auto', `${nomeVisibile(auto)} ${motoreBase(auto?.variante)}`.trim()],
    ['Anno', anno || 'non indicato'],
    ['Intervento', intervento?.nome || altro],
    ['Ricambi', nomeQualita],
    ['Officina', nomeOfficina],
    ['IVA', ivaInclusa ? 'inclusa nel preventivo' : 'esclusa dal preventivo'],
  ];
  return (
    <Domanda titolo="Controlla i dati" onBack={onBack} onNext={onNext} testoAvanti="Verifica">
      {/* l'importo in grande: è il numero che verifichiamo */}
      <div className="riepilogo-importo">
        <span className="riepilogo-importo-etichetta">Preventivo del meccanico</span>
        <span className="riepilogo-importo-valore numeric">{euro(importo)}</span>
      </div>
      <Card>
        <dl className="voci riepilogo-voci">
          {voci.map(([nome, valore]) => (
            <div className="voce" key={nome}>
              <dt className="voce-nome">{nome}</dt>
              <dd className="voce-valore riepilogo-valore">{valore || '—'}</dd>
            </div>
          ))}
        </dl>
      </Card>
      {errore && <Card><p className="muted">{errore}</p></Card>}
    </Domanda>
  );
}
