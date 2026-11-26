// Versioni dell'auto viste dall'utente.
// Nel database lo stesso modello può avere più versioni che differiscono solo per il periodo di produzione
// (per esempio la Fiat 500 prima e dopo il restyling del 2015: stesso motore, freni anteriori diversi).
// All'utente si mostra una sola scheda; la versione giusta si sceglie dopo, con l'anno di immatricolazione.

// "1.2 benzina 69 CV (2007-2015)" -> "1.2 benzina 69 CV"
export function motoreBase(variante) {
  return String(variante?.etichetta || '').replace(/\s*\(\d{4}\s*-\s*\d{4}\)\s*$/, '').trim();
}

// Nome che la gente usa: senza sigla di progetto tra parentesi e senza numero romano della generazione.
// "500 (312)" -> "500", "Clio IV (BH)" -> "Clio", "Crossland X (P17)" -> "Crossland X" (la X fa parte del nome).
export function nomeComune(modelloNome) {
  return String(modelloNome || '')
    .replace(/\s*\([^)]*\)\s*/g, ' ')
    .replace(/\s+(I|II|III|IV|V|VI|VII|VIII)$/u, '')
    .trim();
}

// "Fiat" + "500 (312)" -> "Fiat 500"
export function nomeVisibile(a) {
  return a?.modello ? `${a.marca.nome} ${nomeComune(a.modello.nome)}` : '';
}

const fine = (v) => v.anno_produzione_fine ?? new Date().getFullYear();

// Raggruppa le versioni della marca: stesso modello, stesso motore, stessa alimentazione e cambio = una scheda.
// versioni: [{ marca, modello, variante }] · restituisce [{ chiave, marca, modello, versioni, inizio, fine }]
export function raggruppaVersioni(versioni) {
  const gruppi = new Map();
  for (const a of versioni) {
    const v = a.variante;
    const k = [a.modello.nome, motoreBase(v), v.alimentazione, v.cambio].join('|');
    if (!gruppi.has(k)) gruppi.set(k, { chiave: k, marca: a.marca, modello: a.modello, versioni: [] });
    gruppi.get(k).versioni.push(a);
  }
  return [...gruppi.values()].map((g) => {
    g.versioni.sort((x, y) => x.variante.anno_produzione_inizio - y.variante.anno_produzione_inizio);
    g.inizio = Math.min(...g.versioni.map((a) => a.variante.anno_produzione_inizio));
    g.fine = Math.max(...g.versioni.map((a) => fine(a.variante)));
    g.inCorso = g.versioni.some((a) => a.variante.anno_produzione_fine == null);
    return g;
  });
}

// Anni proposti per l'immatricolazione: tutto il periodo del gruppo, dal più recente
export function anniDelGruppo(gruppo) {
  if (!gruppo) return [];
  const anni = [];
  for (let a = gruppo.fine; a >= gruppo.inizio; a -= 1) anni.push(String(a));
  return anni;
}

// Le versioni compatibili con l'anno scelto ('' = non lo so: tutte).
// Una sola: è quella. Più di una (anno di passaggio, o anno non detto): si chiede all'utente.
export function versioniPerAnno(gruppo, anno) {
  if (!gruppo) return [];
  if (!anno) return gruppo.versioni;
  const a = Number(anno);
  const compatibili = gruppo.versioni.filter((x) => a >= x.variante.anno_produzione_inizio && a <= fine(x.variante));
  return compatibili.length ? compatibili : gruppo.versioni;
}
