export function euro(n) {
  return `€ ${Number(n || 0).toFixed(2).replace('.', ',')}`;
}
export async function copyText(t) {
  try {
    await navigator.clipboard.writeText(t);
    return true;
  } catch {
    return false;
  }
}
