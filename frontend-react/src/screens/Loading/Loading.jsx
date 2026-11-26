import Spinner from '../../components/Spinner/Spinner.jsx';
import Button from '../../components/Button/Button.jsx';
import '../Welcome/Welcome.css';

// Attesa del calcolo: la rotellina resta almeno 3 secondi (lo decide App), poi compare il risultato.
// Rotellina, titolo e spiegazione stanno al centro dello schermo.
export default function Loading({ onCancel }) {
  return (
    <div className="screen screen-attesa">
      <Spinner />
      <h1 className="h1">Calcolo della stima in corso…</h1>
      <p className="muted">Confronto tra prezzi medi dei ricambi, tempi di lavoro e tariffa oraria della tua regione.</p>
      <Button variant="secondary" onClick={onCancel}>Annulla operazione</Button>
    </div>
  );
}
