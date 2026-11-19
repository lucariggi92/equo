export function euro(n) {
  return `€ ${Number(n || 0).toFixed(2).replace('.', ',')}`;
}

// "5.2" -> "+5,2%"; l'API manda i numeri come testo per non perdere decimali
export function percentuale(n, conSegno = true) {
  const v = Number(n || 0);
  return `${conSegno && v > 0 ? '+' : ''}${String(n ?? 0).replace('.', ',')}%`;
}

// "1.50" -> "1,5"
export function numero(n) {
  return String(Number(n || 0)).replace('.', ',');
}

// "2026-11-16" -> "16/11/2026"
export function data(iso) {
  if (!iso) return '';
  const [a, m, g] = String(iso).slice(0, 10).split('-');
  return `${g}/${m}/${a}`;
}

// auto scelta = { marca, modello, variante } -> "Fiat Panda (319)" e "1.2 benzina 69 CV"
export function nomeAuto(auto) {
  return auto?.modello ? `${auto.marca.nome} ${auto.modello.nome}` : '';
}

export async function copyText(t) {
  try {
    await navigator.clipboard.writeText(t);
    return true;
  } catch {
    return false;
  }
}
