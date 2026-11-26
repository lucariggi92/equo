import ProgressBar from '../ProgressBar/ProgressBar.jsx';
import './Avanzamento.css';

// Riga sotto l'intestazione, come nel design della V1 ("Fase 5/5"): domanda corrente e barra di avanzamento.
// testo: al posto di "Domanda N di M: nome" (per esempio "Riepilogo" dopo l'ultima domanda)
export default function Avanzamento({ passo, totale, nome, testo }) {
  const percento = Math.round((passo / totale) * 100);
  return (
    <div className="avanzamento">
      <div className="avanzamento-riga">
        <span>{testo || `Domanda ${passo} di ${totale}: ${nome}`}</span>
        <span className="avanzamento-pct numeric">{percento}%</span>
      </div>
      <ProgressBar value={percento} />
    </div>
  );
}
