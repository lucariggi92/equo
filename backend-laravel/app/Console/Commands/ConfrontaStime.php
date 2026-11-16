<?php

namespace App\Console\Commands;

use App\Models\Intervention;
use App\Models\Region;
use App\Models\Variant;
use App\Services\Stima\CalcoloStima;
use App\Services\Stima\Decimale;
use App\Services\Stima\RigaStima;
use App\Services\Stima\Stima;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Verifica del task L4: per ogni combinazione (versione × intervento × qualità × officina × regione)
 * confronta il calcolo PHP con la procedura MySQL calcola_stima, campo per campo e riga per riga,
 * anche sui verdetti vicini alle soglie. Solo letture: la procedura si chiama con l'utente equo_import.
 */
#[Signature('equo:confronta-stime {--tutte-le-regioni : tutte e 20 le regioni invece di una per area}')]
#[Description('Confronta il calcolo della stima in PHP con la procedura MySQL calcola_stima')]
class ConfrontaStime extends Command
{
    /** Preventivi di prova, come frazione della stima: molto sotto, sulla soglia equo, sulla soglia anomalo, anomalo. */
    private const PREVENTIVI = ['0.70', '1.10', '1.25', '1.40'];

    private int $casi = 0;

    /** @var list<string> */
    private array $differenze = [];

    public function handle(CalcoloStima $calcolo): int
    {
        $regioni = $this->option('tutte-le-regioni')
            ? Region::orderBy('id')->get()
            : Region::whereIn('nome', ['Lombardia', 'Lazio', 'Campania'])->orderBy('id')->get();
        $varianti = Variant::with('modello')->orderBy('id')->get();
        $interventi = Intervention::orderBy('id')->get();
        $pdo = DB::connection('import')->getPdo();

        $totale = $varianti->count() * $interventi->count() * count(CalcoloStima::QUALITA) * count(CalcoloStima::OFFICINE) * $regioni->count();
        $barra = $this->output->createProgressBar($totale);

        foreach ($varianti as $v) {
            foreach ($interventi as $i) {
                foreach (CalcoloStima::QUALITA as $q) {
                    foreach (CalcoloStima::OFFICINE as $o) {
                        foreach ($regioni as $r) {
                            $nome = "{$v->modello->nome} {$v->etichetta} · {$i->nome} · {$q} · {$o} · {$r->nome}";
                            $mysql = $this->procedura($pdo, $v->id, $i->id, $q, $r->id, $o, null);
                            $this->confronta($nome, $mysql, $calcolo->calcola($v->id, $i->id, $q, $r->id, $o));

                            $stima = $mysql['riepilogo']['stima_iva_inclusa'];
                            if ($stima !== null) {
                                foreach (self::PREVENTIVI as $fattore) {
                                    $preventivo = Decimale::arrotonda(Decimale::per($stima, $fattore), 2);
                                    $this->confronta("{$nome} · preventivo {$preventivo}",
                                        $this->procedura($pdo, $v->id, $i->id, $q, $r->id, $o, $preventivo),
                                        $calcolo->calcola($v->id, $i->id, $q, $r->id, $o, $preventivo));
                                }
                            }
                            $barra->advance();
                        }
                    }
                }
            }
        }
        $barra->finish();
        $this->newLine(2);

        $this->line("Casi confrontati: {$this->casi}");
        $this->line('Identici: '.($this->casi - count($this->differenze)));
        if ($this->differenze === []) {
            $this->info('Nessuna differenza tra PHP e MySQL.');

            return self::SUCCESS;
        }
        $this->error('Differenze: '.count($this->differenze));
        foreach (array_slice($this->differenze, 0, 20) as $d) {
            $this->line("  {$d}");
        }

        return self::FAILURE;
    }

    /** @return array{dettaglio: list<array<string, ?string>>, riepilogo: array<string, ?string>} */
    private function procedura(PDO $pdo, int $v, int $i, string $q, int $r, string $o, ?string $preventivo): array
    {
        $st = $pdo->prepare('CALL calcola_stima(?, ?, ?, ?, ?, ?)');
        $st->execute([$v, $i, $q, $r, $o, $preventivo]);
        $dettaglio = $st->fetchAll(PDO::FETCH_ASSOC);
        $st->nextRowset();
        $riepilogo = $st->fetch(PDO::FETCH_ASSOC);
        while ($st->nextRowset()) {
            // consuma gli altri risultati della CALL
        }
        $st->closeCursor();

        return ['dettaglio' => $dettaglio, 'riepilogo' => $riepilogo];
    }

    /** @param array{dettaglio: list<array<string, ?string>>, riepilogo: array<string, ?string>} $mysql */
    private function confronta(string $nome, array $mysql, Stima $php): void
    {
        $this->casi++;
        $s = fn ($x) => $x === null ? null : (is_bool($x) ? ($x ? '1' : '0') : (string) $x);
        $m = $mysql['riepilogo'];
        $p = $php->toArray();
        $campi = [
            'ricambi_netto' => $php->ricambiNetto, 'ricarico_netto' => $php->ricaricoNetto, 'manodopera_netto' => $php->manodoperaNetto,
            'imponibile' => $php->imponibile, 'iva' => $php->iva, 'stima_iva_inclusa' => $php->stimaIvaInclusa,
            'stima_da' => $php->stimaDa, 'stima_a' => $php->stimaA,
            'ricambi_richiesti' => $php->ricambiRichiesti, 'ricambi_con_prezzo' => $php->ricambiConPrezzo,
            'ricambi_verificati_pct' => $php->ricambiVerificatiPct, 'ricambi_indicativi_pct' => $php->ricambiIndicativiPct,
            'ricambi_stimati_pct' => $php->ricambiStimatiPct, 'manodopera_presente' => $php->manodoperaPresente,
            'completo' => $php->completo, 'scostamento_pct' => $php->scostamentoPct, 'esito' => $php->esito, 'avviso' => $php->avviso,
        ];
        foreach ($campi as $campo => $valore) {
            if ($s($m[$campo]) !== $s($valore)) {
                $this->differenze[] = "{$nome}: {$campo} MySQL=".var_export($m[$campo], true).' PHP='.var_export($s($valore), true);

                return;
            }
        }

        $mancantiMysql = $m['mancanti'] === null ? [] : explode(', ', $m['mancanti']);
        $mancantiPhp = $php->mancanti;
        sort($mancantiMysql);
        sort($mancantiPhp);
        if ($mancantiMysql !== $mancantiPhp) {
            $this->differenze[] = "{$nome}: mancanti MySQL=".implode(', ', $mancantiMysql).' PHP='.implode(', ', $mancantiPhp);

            return;
        }

        $colonne = ['tipo', 'voce', 'quantita', 'produttore', 'codice', 'prezzo_unitario_netto', 'metodo', 'ricarico_pct', 'totale_netto', 'da', 'a'];
        $righeMysql = array_map(fn ($riga) => array_map($s, array_intersect_key($riga, array_flip($colonne))), $mysql['dettaglio']);
        $righePhp = array_map(fn (array $riga) => array_map($s, array_intersect_key($riga, array_flip($colonne))), $p['dettaglio']);
        if ($righeMysql !== $righePhp) {
            $this->differenze[] = "{$nome}: dettaglio diverso\n    MySQL ".json_encode($righeMysql)."\n    PHP   ".json_encode($righePhp);
        }
    }
}
