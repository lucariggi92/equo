// Testo del messaggio per il meccanico (la chiamata all'API è in client.js).
// Tono cordiale: chiede di capire la differenza, non accusa.
export function messaggioMeccanico({ auto, intervento, prezzo, stima }) {
  return `Buongiorno, ho confrontato il preventivo da ${prezzo} per ${intervento} su ${auto} con una stima basata sui prezzi medi dei ricambi e sulle tariffe della mia zona (circa ${stima}, IVA inclusa). Potreste aiutarmi a capire la differenza? Grazie!`;
}
