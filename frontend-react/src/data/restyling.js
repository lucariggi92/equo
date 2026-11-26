// Come distinguere a vista le versioni di uno stesso modello (prima e dopo un restyling),
// mostrato solo se l'anno di immatricolazione non basta (anno di passaggio o "non lo so").
// Chiave: marca + modello come arrivano dall'API; per ogni versione: anno di inizio produzione -> foto e descrizione.
// Foto da Wikimedia Commons: autore e licenza sono citati sotto la foto.
export const DIFFERENZE = {
  "Fiat 500 (312)": {
    domanda: "Com'è il fanale posteriore della tua 500?",
    suggerimento: "Nel 2015 la 500 è stata rinnovata: la differenza più facile da vedere sono i fanali posteriori.",
    versioni: {
      2007: { titolo: 'Fanale tutto rosso', sotto: 'Senza buco al centro. Prima serie, 2007-2015', foto: {"src":"/modelli/fiat-500-2007-retro.jpg","autore":"Rudolf Stricker","licenza":"Attribution","pagina":"https://commons.wikimedia.org/wiki/File:Fiat_500_rear_20080621.jpg"} },
      2015: { titolo: 'Fanale ad anello', sotto: 'Centro del colore della carrozzeria. Restyling, 2015-2020', foto: {"src":"/modelli/fiat-500-2015-retro.jpg","autore":"Antoineets","licenza":"CC BY-SA 4.0","pagina":"https://commons.wikimedia.org/wiki/File:Fiat_500_(2015_facelift)_Riva_(cropped).jpg"} },
    },
  },
};
