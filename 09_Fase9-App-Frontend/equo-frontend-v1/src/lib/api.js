// Backend = n8n. Il frontend invia solo nomi + prezzo; tariffe, ore e
// ricambi vivono nel workflow n8n (benchmark o agente AI).
const WEBHOOK_URL = import.meta.env.VITE_N8N_VERIFICA_URL || '';

const pickName = (v) => (v && typeof v === 'object' ? (v.nome || v.modello || v) : v);

export async function verificaPreventivo({ regione, auto, intervento, prezzo, note }) {
  const body = {
    regione: pickName(regione),
    auto:
      auto && typeof auto === 'object'
        ? { modello: auto.modello, motore: auto.motore || '' }
        : { modello: String(auto || ''), motore: '' },
    intervento: pickName(intervento),
    prezzo,
    note: note || '',
  };

  if (WEBHOOK_URL) {
    const res = await fetch(WEBHOOK_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`Backend n8n: HTTP ${res.status}`);
    return res.json();
  }

  return mockDev(body);
}

// Solo sviluppo senza n8n: NON usare come fonte prezzi in produzione.
async function mockDev({ prezzo }) {
  await new Promise((r) => setTimeout(r, 1800));
  const manodopera = 120;
  const ricambi = 95;
  const stima = manodopera + ricambi;
  const scostamento = Math.round(prezzo - stima);
  const pct = scostamento / stima;
  return {
    manodopera,
    ricambi,
    stima,
    scostamento,
    verdetto: pct <= 0.1 ? 'equo' : pct <= 0.25 ? 'sopra-media' : 'anomalo',
    fonte: 'mock-dev',
  };
}

export function messaggioMeccanico({ regione, auto, intervento, prezzo, stima }) {
  const r = pickName(regione);
  const a = auto && typeof auto === 'object' ? auto.modello : auto;
  const iv = pickName(intervento);
  return `Buongiorno, ho confrontato il preventivo da € ${prezzo} per ${iv} su ${a} (${r}) con i benchmark regionali (stima equa circa € ${stima}). Potreste aiutarmi a capire la differenza? Grazie!`;
}
