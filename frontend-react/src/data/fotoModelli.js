// Foto dei modelli da Wikimedia Commons (licenze libere: citare autore e licenza, vedi il footer e la schermata del modello).
// Chiave: marca, modello e anno di inizio produzione della versione, come arrivano dall'API.
// Una versione senza foto mostra il nome del modello al posto dell'immagine.
export const FOTO_MODELLI = {
  "Dacia Sandero II 2013": {
    "src": "/modelli/dacia-sandero-2.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Dacia_Sandero_TCe_90_eco%C2%B2_Laur%C3%A9ate_(II)_%E2%80%93_Frontansicht,_21._April_2013,_M%C3%BCnster.jpg"
  },
  "Fiat 500 (312) 2007": {
    "src": "/modelli/fiat-500-2007.jpg",
    "autore": "Rudolf Stricker",
    "licenza": "Attribution",
    "pagina": "https://commons.wikimedia.org/wiki/File:Fiat_500_front_20080621.jpg"
  },
  "Fiat 500 (312) 2015": {
    "src": "/modelli/fiat-500-2015.jpg",
    "autore": "Vauxford",
    "licenza": "CC BY-SA 4.0",
    "pagina": "https://commons.wikimedia.org/wiki/File:2017_Fiat_500_facelift.jpg"
  },
  "Fiat Panda (319) 2012": {
    "src": "/modelli/fiat-panda-319.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Fiat_Panda_1.2_8V_Lounge_(III)_%E2%80%93_Frontansicht,_25._Februar_2012,_D%C3%BCsseldorf.jpg"
  },
  "Fiat Punto (199) 2005": {
    "src": "/modelli/fiat-punto-199.jpg",
    "autore": "Rudolf Stricker",
    "licenza": "CC BY-SA 3.0",
    "pagina": "https://commons.wikimedia.org/wiki/File:Fiat_Punto_front_20070920.jpg"
  },
  "Ford Fiesta VI 2008": {
    "src": "/modelli/ford-fiesta-6.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Ford_Fiesta_1.0_EcoBoost_Titanium_(VII,_Facelift)_%E2%80%93_Frontansicht,_2._Mai_2015,_D%C3%BCsseldorf.jpg"
  },
  "Lancia Ypsilon (846) 2011": {
    "src": "/modelli/lancia-ypsilon-846.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Lancia_Ypsilon_0.9_TwinAir_Turbo_8v_Platinum_(II)_%E2%80%93_Frontansicht,_31._Dezember_2012,_D%C3%BCsseldorf.jpg"
  },
  "Opel Crossland X (P17) 2018": {
    "src": "/modelli/opel-crossland-x.jpg",
    "autore": "Lothar Spurzem",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Opel_Crossland_X_Ecotec_(2024-06-17_Sp_ret).JPG"
  },
  "Peugeot 208 I 2012": {
    "src": "/modelli/peugeot-208-1.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Peugeot_208_95_VTi_Active_%E2%80%93_Frontansicht,_5._Mai_2012,_D%C3%BCsseldorf.jpg"
  },
  "Renault Clio IV (BH) 2012": {
    "src": "/modelli/renault-clio-4.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Renault_Clio_TCe_90_Luxe_(IV)_%E2%80%93_Frontansicht,_17._Mai_2013,_M%C3%BCnster.jpg"
  },
  "Toyota Yaris III (XP13) 2012": {
    "src": "/modelli/toyota-yaris-3.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:Toyota_Yaris_(XP130)_%E2%80%93_Frontansicht,_21._Juli_2012,_Heiligenhaus.jpg"
  },
  "Volkswagen Golf VII (5G1) 2012": {
    "src": "/modelli/vw-golf-7.jpg",
    "autore": "M 93",
    "licenza": "CC BY-SA 3.0 de",
    "pagina": "https://commons.wikimedia.org/wiki/File:VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_(VII)_%E2%80%93_Frontansicht,_15._Juni_2014,_D%C3%BCsseldorf.jpg"
  }
};

export function fotoModello(a) {
  if (!a) return null;
  return FOTO_MODELLI[`${a.marca.nome} ${a.modello.nome} ${a.variante.anno_produzione_inizio}`] || null;
}
