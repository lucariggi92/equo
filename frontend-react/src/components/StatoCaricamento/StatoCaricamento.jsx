import Card from '../Card/Card.jsx';
import Button from '../Button/Button.jsx';

// Mostra "caricamento" mentre arrivano i dati dall'API, oppure l'errore con il pulsante per riprovare.
export default function StatoCaricamento({ errore, onRiprova }) {
  if (!errore) return <p className="muted">Caricamento…</p>;
  return (
    <Card>
      <p className="muted">{errore}</p>
      <Button variant="secondary" onClick={onRiprova}>Riprova</Button>
    </Card>
  );
}
