import { useState } from 'react';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Badge from '../../components/Badge/Badge.jsx';
import Semaforo from '../../components/Semaforo/Semaforo.jsx';
import MessageBox from '../../components/MessageBox/MessageBox.jsx';
import { euro, percentuale, numero, data, nomeAuto, copyText } from '../../lib/format.js';
import { messaggioMeccanico } from '../../api/verifica.js';
import '../Welcome/Welcome.css';
import './Report.css';

// esiti dell'API: equo fino a +10%, sopra la media fino a +25%, oltre anomalo; senza dati sufficienti nessun verdetto
const META = {
  equo: { tone: 'ok', label: 'Preventivo Equo' },
  sopra_media: { tone: 'warn', label: 'Preventivo Sopra la Media' },
  anomalo: { tone: 'err', label: 'Preventivo Anomalo' },
  dati_insufficienti: { tone: 'info', label: 'Dati insufficienti per un verdetto' },
};

// affidabilità del prezzo di ogni riga (scala D5)
const METODO = { rilevato: 'verificato', indicativo: 'indicativo' };
const metodo = (m) => METODO[m] || 'stimato';

export default function Report({ auto, intervento, result, prezziAl, onReset }) {
  const [copied, setCopied] = useState(false);
  const [dettaglioAperto, setDettaglioAperto] = useState(false);

  // intervento non in elenco: la richiesta è solo registrata
  if (result.esito === 'non_calcolabile') {
    return (
      <div className="screen">
        <div className="center"><Badge tone="info">Richiesta registrata</Badge></div>
        <h1 className="verdict-title">Non possiamo ancora stimarlo</h1>
        <Card><p className="muted">{result.messaggio}</p></Card>
        <Button variant="secondary" onClick={onReset}>Nuova verifica</Button>
      </div>
    );
  }

  const { riepilogo: r, verdetto: v, dettaglio } = result;
  const m = META[v.esito] || META.dati_insufficienti;
  const preventivo = result.preventivo_iva_inclusa;
  const differenza = Number(preventivo) - Number(r.stima_iva_inclusa);
  const ricambi = dettaglio.filter((d) => d.tipo === 'ricambio');
  const manodopera = dettaglio.find((d) => d.tipo === 'manodopera');
  const msg = r.completo
    ? messaggioMeccanico({ auto: nomeAuto(auto), intervento: intervento?.nome, prezzo: euro(preventivo), stima: euro(r.stima_iva_inclusa) })
    : null;

  return (
    <div className="screen">
      <div className="center"><Badge tone={m.tone}>Verdetto Finale</Badge></div>
      {m.tone !== 'info' && <div className="center"><Semaforo tone={m.tone} large /></div>}
      <h1 className="verdict-title">{m.label}</h1>
      {/* il prezzo calcolato, in grande, subito dopo l'attesa */}
      {r.completo && (
        <>
          <div className="center"><span className="msg-label">Stima equa, IVA inclusa</span></div>
          <div className="amount numeric">{euro(r.stima_iva_inclusa)}</div>
        </>
      )}
      {/* subito dopo il prezzo equo, in evidenza: quanto il preventivo è sopra o sotto, in euro */}
      {r.completo && (
        <div className={`scarto scarto-${m.tone}`}>
          {Math.abs(differenza) < 0.005 ? (
            <span className="scarto-valore">In linea con la stima</span>
          ) : (
            <>
              <span className="scarto-valore numeric">{differenza > 0 ? '+' : '−'}{euro(Math.abs(differenza))}</span>
              <span className="scarto-testo">{differenza > 0 ? 'in più' : 'in meno'} rispetto alla stima equa</span>
            </>
          )}
          <span className="scarto-dettaglio numeric">Il tuo preventivo: {euro(preventivo)} ({percentuale(v.scostamento_pct)})</span>
        </div>
      )}
      {v.avviso && <Card><p className="muted">Attenzione: {v.avviso}.</p></Card>}
      {!r.completo && (
        <Card title="Cosa manca">
          <p className="muted">
            Non abbiamo ancora tutti i prezzi per questo intervento su questa versione, quindi non diamo un verdetto.
            {r.mancanti.length > 0 && ` Mancano: ${r.mancanti.join(', ')}.`}
            {!r.manodopera_presente && ' Manca il tempo di manodopera.'}
          </p>
        </Card>
      )}

      {/* come nella V1: una sola scheda con le voci principali */}
      <Card title="Spaccato stima">
        <div className="recap">
          <span>Manodopera stimata: <strong className="numeric">{euro(r.manodopera_netto)}</strong></span>
          <span>Ricambi e materiali: <strong className="numeric">{euro(Number(r.ricambi_netto) + Number(r.ricarico_netto))}</strong></span>
          <span>IVA: <strong className="numeric">{euro(r.iva)}</strong></span>
          <span>{r.completo ? 'Stima equa totale' : 'Stima parziale (solo voci con prezzo)'}: <strong className="numeric">{euro(r.stima_iva_inclusa)}</strong></span>
        </div>
      </Card>

      {/* il dettaglio resta tutto, ma chiuso finché non lo si apre */}
      <Button variant="secondary" onClick={() => setDettaglioAperto((a) => !a)}>
        {dettaglioAperto ? 'Nascondi il dettaglio' : 'Vedi il dettaglio della stima'}
      </Button>
      {dettaglioAperto && (
        <>
          <Card title="Dettaglio">
            <div className="recap">
              <span>Ricambi: <strong className="numeric">{euro(r.ricambi_netto)}</strong></span>
              <span>Ricarico dell'officina sui ricambi: <strong className="numeric">{euro(r.ricarico_netto)}</strong></span>
              {manodopera && <span>Manodopera: <strong className="numeric">{numero(manodopera.quantita)} ore × {euro(manodopera.prezzo_unitario_netto)}/ora</strong></span>}
              {r.completo && <span>Forbice della stima: <strong className="numeric">da {euro(r.stima_da)} a {euro(r.stima_a)}</strong></span>}
              <span>Il tuo preventivo, IVA inclusa: <strong className="numeric">{euro(preventivo)}</strong>{r.completo ? ` (${percentuale(v.scostamento_pct)})` : ''}</span>
            </div>
          </Card>
          {ricambi.length > 0 && (
            <Card title="Ricambi considerati">
              <div className="recap">
                {ricambi.map((d) => (
                  <span key={d.voce}>
                    {d.voce}{Number(d.quantita) !== 1 ? ` × ${numero(d.quantita)}` : ''}:{' '}
                    <strong className="numeric">{d.totale_netto === null ? 'prezzo mancante' : euro(d.totale_netto)}</strong>
                    {d.totale_netto !== null && ` · ${metodo(d.metodo)}`}
                  </span>
                ))}
              </div>
            </Card>
          )}
          <Card title="Quanto sono affidabili questi numeri">
            <div className="recap">
              {r.ricambi_verificati_pct !== null && (
                <span>Valore dei ricambi: <strong>{r.ricambi_verificati_pct}% verificato</strong> (2 o più negozi), {r.ricambi_indicativi_pct}% indicativo (1 negozio), {r.ricambi_stimati_pct}% stimato.</span>
              )}
              {manodopera && <span>Tempo e tariffa della manodopera: <strong>{manodopera.metodo === 'rilevato' ? 'verificati' : 'stimati'}</strong>.</span>}
              {prezziAl && <span>Prezzi dei ricambi aggiornati al <strong>{data(prezziAl)}</strong>.</span>}
              <span>Ricambi e manodopera sono netti; il totale aggiunge l'IVA.</span>
            </div>
          </Card>
        </>
      )}

      {msg && (
        <>
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
        </>
      )}
      <Button variant="secondary" onClick={onReset}>Nuova verifica</Button>
    </div>
  );
}
