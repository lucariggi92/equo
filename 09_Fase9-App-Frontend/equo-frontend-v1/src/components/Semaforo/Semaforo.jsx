import './Semaforo.css';

export default function Semaforo({ tone = 'ok', large }) {
  return <span className={`semaforo semaforo-${tone}${large ? ' semaforo-large' : ''}`} />;
}
