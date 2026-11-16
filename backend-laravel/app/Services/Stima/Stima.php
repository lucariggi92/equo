<?php

namespace App\Services\Stima;

/**
 * Risultato del calcolo: dettaglio riga per riga, riepilogo e (se c'è un preventivo) verdetto.
 * Stessi campi e stessi valori della procedura MySQL calcola_stima.
 */
final readonly class Stima
{
    /**
     * @param  list<RigaStima>  $righe
     * @param  list<string>  $mancanti
     */
    public function __construct(
        public array $righe,
        public ?string $ricambiNetto,
        public ?string $ricaricoNetto,
        public ?string $manodoperaNetto,
        public ?string $imponibile,
        public ?string $iva,
        public ?string $stimaIvaInclusa,
        public ?string $stimaDa,
        public ?string $stimaA,
        public ?int $ricambiRichiesti,
        public ?int $ricambiConPrezzo,
        public ?string $ricambiVerificatiPct,
        public ?string $ricambiIndicativiPct,
        public ?string $ricambiStimatiPct,
        public ?bool $manodoperaPresente,
        public bool $completo,
        public array $mancanti,
        public ?string $preventivo,
        public ?string $scostamentoPct,
        public ?string $esito,
        public ?string $avviso,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'dettaglio' => array_map(fn (RigaStima $r) => $r->toArray(), $this->righe),
            'riepilogo' => [
                'ricambi_netto' => $this->ricambiNetto,
                'ricarico_netto' => $this->ricaricoNetto,
                'manodopera_netto' => $this->manodoperaNetto,
                'imponibile' => $this->imponibile,
                'iva' => $this->iva,
                'stima_iva_inclusa' => $this->stimaIvaInclusa,
                'stima_da' => $this->stimaDa,
                'stima_a' => $this->stimaA,
                'ricambi_richiesti' => $this->ricambiRichiesti,
                'ricambi_con_prezzo' => $this->ricambiConPrezzo,
                'ricambi_verificati_pct' => $this->ricambiVerificatiPct,
                'ricambi_indicativi_pct' => $this->ricambiIndicativiPct,
                'ricambi_stimati_pct' => $this->ricambiStimatiPct,
                'manodopera_presente' => $this->manodoperaPresente,
                'completo' => $this->completo,
                'mancanti' => $this->mancanti,
            ],
            'verdetto' => [
                'preventivo' => $this->preventivo,
                'scostamento_pct' => $this->scostamentoPct,
                'esito' => $this->esito,
                'avviso' => $this->avviso,
            ],
        ];
    }
}
