<?php

namespace App\Services\Stima;

/**
 * Una riga del dettaglio: un ricambio (con il suo prezzo e ricarico) oppure la manodopera.
 * Importi netti di IVA, come stringhe decimali a 4 cifre; null quando il dato manca.
 */
final readonly class RigaStima
{
    public function __construct(
        public string $tipo,                 // 'ricambio' | 'manodopera'
        public string $voce,
        public ?string $quantita,
        public ?string $produttore = null,
        public ?string $codice = null,
        public ?bool $generico = null,
        public ?string $prezzoNetto = null,
        public ?string $tipoFonte = null,
        public ?string $metodo = null,
        public ?bool $daAvvalorare = null,
        public ?string $ricarico = null,
        public ?string $ricambioNetto = null,
        public ?string $ricaricoNetto = null,
        public ?string $totaleNetto = null,
        public ?string $totaleMin = null,
        public ?string $totaleMax = null,
    ) {}

    public function eRicambio(): bool
    {
        return $this->tipo === 'ricambio';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'tipo' => $this->tipo,
            'voce' => $this->voce,
            'quantita' => $this->quantita,
            'produttore' => $this->produttore,
            'codice' => $this->codice,
            'generico' => $this->generico,
            'prezzo_unitario_netto' => $this->prezzoNetto === null ? null : Decimale::arrotonda($this->prezzoNetto, 2),
            'metodo' => $this->metodo,
            'ricarico_pct' => $this->ricarico === null ? null : Decimale::arrotonda(Decimale::per($this->ricarico, '100'), 0),
            'totale_netto' => $this->totaleNetto === null ? null : Decimale::arrotonda($this->totaleNetto, 2),
            'da' => $this->totaleMin === null ? null : Decimale::arrotonda($this->totaleMin, 2),
            'a' => $this->totaleMax === null ? null : Decimale::arrotonda($this->totaleMax, 2),
        ];
    }
}
