// Collegamento all'API Laravel (decisione D8): tre chiamate, solo JSON, nessun cookie.
// In sviluppo l'API gira su http://localhost:8000; online si imposta VITE_API_URL (vedi .env.example).
const BASE = (import.meta.env.VITE_API_URL || (import.meta.env.DEV ? 'http://localhost:8000/api' : '/api')).replace(/\/$/, '');
const ATTESA_MASSIMA_MS = 15000;

export class ErroreApi extends Error {
  constructor(messaggio, { stato = 0, campi = {} } = {}) {
    super(messaggio);
    this.name = 'ErroreApi';
    this.stato = stato;
    this.campi = campi; // per gli errori 422: { campo: ['messaggio', ...] }
  }
}

function attesa(secondi) {
  if (secondi <= 60) return 'tra un minuto';
  if (secondi < 3600) return `tra ${Math.ceil(secondi / 60)} minuti`;
  return 'più tardi';
}

async function richiesta(percorso, { metodo = 'GET', dati } = {}) {
  const controllo = new AbortController();
  const timer = setTimeout(() => controllo.abort(), ATTESA_MASSIMA_MS);
  let risposta;
  try {
    risposta = await fetch(`${BASE}${percorso}`, {
      method: metodo,
      headers: { Accept: 'application/json', ...(dati ? { 'Content-Type': 'application/json' } : {}) },
      body: dati ? JSON.stringify(dati) : undefined,
      credentials: 'omit',
      signal: controllo.signal,
    });
  } catch (e) {
    throw new ErroreApi(e.name === 'AbortError'
      ? 'Il servizio non risponde. Riprova tra poco.'
      : 'Servizio non raggiungibile. Controlla la connessione e riprova.');
  } finally {
    clearTimeout(timer);
  }

  const corpo = await risposta.json().catch(() => null);
  if (risposta.ok && corpo) return corpo;
  if (risposta.status === 422) {
    const campi = corpo?.errors || {};
    throw new ErroreApi(Object.values(campi)[0]?.[0] || 'Controlla i dati inseriti.', { stato: 422, campi });
  }
  if (risposta.status === 429) {
    const secondi = Number(risposta.headers.get('Retry-After')) || 60;
    throw new ErroreApi(`Troppe verifiche ravvicinate. Riprova ${attesa(secondi)}.`, { stato: 429 });
  }
  throw new ErroreApi('Il servizio ha avuto un problema. Riprova tra poco.', { stato: risposta.status });
}

// Elenchi del form: marche con i modelli, interventi, qualità, tipi di officina, regioni.
export const opzioni = () => richiesta('/opzioni');

// Versioni di un modello; con l'anno, solo quelle prodotte in quell'anno.
export function varianti(modelloId, anno) {
  const q = new URLSearchParams({ modello_id: String(modelloId) });
  if (anno) q.set('anno', String(anno));
  return richiesta(`/varianti?${q}`);
}

// Stima e verdetto; la richiesta viene salvata in forma anonima.
export const verifica = (dati) => richiesta('/verifica', { metodo: 'POST', dati });
