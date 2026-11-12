# Backend Laravel

API di EQUO: legge il database `equo` (vedi [`../database-mysql`](../database-mysql/README.md)), calcola la stima e il verdetto e salva le richieste anonime in `quote_requests`.

**Stato:** da iniziare. La fase Laravel parte solo dopo la chiusura della fase MySQL, cioè quando i prezzi reali dell'Onda A sono raccolti e caricati.

Rotte previste:

| Metodo | Rotta | Cosa fa |
|---|---|---|
| GET | `/api/opzioni` | regioni, marche, modelli, interventi, qualità, tipi officina |
| GET | `/api/varianti?modello_id=` | varianti di un modello |
| POST | `/api/verifica` | stima + verdetto, salva la richiesta |
| GET | `/api/salute` | stato del servizio e del database |
