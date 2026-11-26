import { useState } from 'react';
import Button from '../../components/Button/Button.jsx';
import Card from '../../components/Card/Card.jsx';
import Badge from '../../components/Badge/Badge.jsx';
import MessageBox from '../../components/MessageBox/MessageBox.jsx';
import { euro, percentuale, numero, nomeAuto, copyText } from '../../lib/format.js';
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
const TONO_METODO = { verificato: 'ok', indicativo: 'warn', stimato: 'info' };

// una voce del dettaglio: nome a sinistra, valore in grassetto a destra, nota piccola sotto
function Voce({ nome, valore, nota, totale, mancante }) {
  return (
    <div className={`voce${totale ? ' voce-totale' : ''}${mancante ? ' voce-mancante' : ''}`}>
      <dt className="voce-nome">{nome}</dt>
      <dd className="voce-valore numeric">{valore}</dd>
      {nota && <dd className="voce-nota">{nota}</dd>}
    </div>
  );
}
const Voci = ({ children }) => <dl className="voci">{children}</dl>;

export default function Report({ auto, intervento, result, onReset }) {
  const [copied, setCopied] = useState(false);
  const [dettaglioAperto, setDettaglioAperto] = useState(false);

  // intervento non in elenco: la richiesta è solo registrata
  if (result.esito === 'non_calcolabile') {
    return (
      <div className="screen">
        <div className="verdetto">
          <Badge tone="info">Richiesta registrata</Badge>
          <h1 className="verdict-title">Non possiamo ancora stimarlo</h1>
        </div>
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
      {/* il verdetto è un'etichetta di stato grande che fa da titolo: verde acqua equo, bordata sopra la media, nera anomalo */}
      <div className="verdetto">
        <h1 className={`verdetto-etichetta verdetto-${m.tone}`}>{m.label}</h1>
        {/* il prezzo calcolato, in grande, subito dopo l'attesa */}
        {r.completo && (
          <div className="stima">
            <span className="stima-etichetta">Stima equa, IVA inclusa</span>
            <div className="amount numeric">{euro(r.stima_iva_inclusa)}</div>
          </div>
        )}
      </div>
      {/* subito dopo il prezzo equo, in evidenza: quanto il preventivo è sopra o sotto, in euro */}
      {r.completo && (
        <div className={`scarto scarto-${m.tone}`}>
          {Math.abs(differenza) < 0.005 ? (
            <span className="scarto-valore scarto-testo-solo">In linea con la stima</span>
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
        <Voci>
          <Voce nome="Manodopera stimata" valore={euro(r.manodopera_netto)} />
          <Voce nome="Ricambi e materiali" valore={euro(Number(r.ricambi_netto) + Number(r.ricarico_netto))} />
          <Voce nome="IVA" valore={euro(r.iva)} />
          <Voce totale nome={r.completo ? 'Stima equa totale' : 'Stima parziale (solo voci con prezzo)'} valore={euro(r.stima_iva_inclusa)} />
        </Voci>
      </Card>

      {/* il dettaglio resta tutto, ma chiuso finché non lo si apre */}
      <Button variant="secondary" onClick={() => setDettaglioAperto((a) => !a)}>
        {dettaglioAperto ? 'Nascondi il dettaglio' : 'Vedi il dettaglio della stima'}
      </Button>
      {dettaglioAperto && (
        <>
          <Card title="Dettaglio">
            <Voci>
              <Voce nome="Ricambi" valore={euro(r.ricambi_netto)} />
              <Voce nome="Ricarico dell'officina sui ricambi" valore={euro(r.ricarico_netto)} />
              {manodopera && (
                <Voce nome="Manodopera" valore={`${numero(manodopera.quantita)} ore`} nota={`a ${euro(manodopera.prezzo_unitario_netto)}/ora`} />
              )}
              {r.completo && <Voce nome="Forbice della stima" valore={`${euro(r.stima_da)} – ${euro(r.stima_a)}`} />}
              <Voce
                totale
                nome="Il tuo preventivo, IVA inclusa"
                valore={euro(preventivo)}
                nota={r.completo ? `${percentuale(v.scostamento_pct)} rispetto alla stima` : null}
              />
            </Voci>
          </Card>
          {ricambi.length > 0 && (
            <Card title="Ricambi considerati">
              <Voci>
                {ricambi.map((d) => (
                  <Voce
                    key={d.voce}
                    nome={`${d.voce}${Number(d.quantita) !== 1 ? ` × ${numero(d.quantita)}` : ''}`}
                    valore={d.totale_netto === null ? 'prezzo mancante' : euro(d.totale_netto)}
                    mancante={d.totale_netto === null}
                    nota={d.totale_netto !== null ? <Badge tone={TONO_METODO[metodo(d.metodo)]}>{metodo(d.metodo)}</Badge> : null}
                  />
                ))}
              </Voci>
            </Card>
          )}
          {/* la scheda "Quanto sono affidabili questi numeri" e tolta su richiesta del PM (25/11/2026) */}
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
