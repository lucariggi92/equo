import { useState } from 'react';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Badge from '../../components/Badge/Badge.jsx';
import Semaforo from '../../components/Semaforo/Semaforo.jsx';
import MessageBox from '../../components/MessageBox/MessageBox.jsx';
import { euro, copyText } from '../../lib/format.js';
import { messaggioMeccanico } from '../../lib/api.js';
import '../Welcome/Welcome.css';

const META = {
  equo: { tone: 'ok', label: 'Preventivo Equo', badge: 'ok', sema: 'ok' },
  'sopra-media': { tone: 'warn', label: 'Preventivo Sopra la Media', badge: 'warn', sema: 'warn' },
  anomalo: { tone: 'err', label: 'Preventivo Anomalo', badge: 'err', sema: 'err' },
};

export default function Report({ regione, auto, intervento, prezzo, result, onReset }) {
  const [copied, setCopied] = useState(false);
  const m = META[result.verdetto] || META.equo;
  const msg = messaggioMeccanico({ regione, auto, intervento, prezzo, stima: result.stima });
  return (
    <div className="screen">
      <div className="center"><Badge tone={m.badge}>Verdetto Finale</Badge></div>
      <div className="center"><Semaforo tone={m.sema} large /></div>
      <h1 className="verdict-title">{m.label}</h1>
      {result.errore && <p className="muted">Backend non raggiungibile ({result.errore}). Controlla n8n e riprova.</p>}
      <div className="center">
        <Badge tone={m.badge}>
          {result.scostamento >= 0 ? '+' : ''}{euro(result.scostamento)} rispetto alla stima equa
        </Badge>
      </div>
      <Card title="Spaccato stima">
        <div className="recap">
          <span>Manodopera stimata: <strong className="numeric">{euro(result.manodopera)}</strong></span>
          <span>Ricambi e materiali: <strong className="numeric">{euro(result.ricambi)}</strong></span>
          <span>Stima equa totale: <strong className="numeric">{euro(result.stima)}</strong></span>
        </div>
      </Card>
      <span className="msg-label">Bozza WhatsApp / SMS</span>
      <MessageBox text={msg} />
      <Button
        onClick={async () => {
          const ok = await copyText(msg);
          setCopied(ok);
          setTimeout(() => setCopied(false), 2000);
        }}
      >
        {copied ? 'Copiato!' : 'Copia messaggio per il meccanico'}
      </Button>
      <Button variant="secondary" onClick={onReset}>Nuova verifica</Button>
    </div>
  );
}
