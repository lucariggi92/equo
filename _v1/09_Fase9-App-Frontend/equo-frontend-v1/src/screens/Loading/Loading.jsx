import { useEffect, useState } from 'react';
import Card from '../../components/Card/Card.jsx';
import ProgressBar from '../../components/ProgressBar/ProgressBar.jsx';
import Button from '../../components/Button/Button.jsx';
import '../Welcome/Welcome.css';

export default function Loading({ onCancel }) {
  const [p, setP] = useState(5);
  useEffect(() => {
    const t = setInterval(() => setP((v) => Math.min(95, v + 9)), 220);
    return () => clearInterval(t);
  }, []);
  return (
    <div className="screen">
      <h1 className="h1">Analisi dei listini in corso...</h1>
      <p className="muted">Confronto costi orari manodopera regionale e prezzi medi ricambi OEM/Aftermarket.</p>
      <ProgressBar value={p} />
      <Card><p className="muted numeric">{p}% • banche dati tempario 2024</p></Card>
      <Button variant="secondary" onClick={onCancel}>Annulla operazione</Button>
    </div>
  );
}
