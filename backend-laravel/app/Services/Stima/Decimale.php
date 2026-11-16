<?php

namespace App\Services\Stima;

/**
 * Aritmetica decimale esatta (bcmath), con gli stessi arrotondamenti di MySQL:
 * ROUND arrotonda "lontano dallo zero" sul 5, la divisione tiene 4 cifre in più del dividendo.
 * Gli importi viaggiano come stringhe ("8.0615"), mai come numeri con la virgola mobile.
 */
final class Decimale
{
    private const SCALA = 14;

    public static function somma(string ...$valori): string
    {
        $tot = '0';
        foreach ($valori as $v) {
            $tot = bcadd($tot, $v, self::SCALA);
        }

        return $tot;
    }

    public static function meno(string $a, string $b): string
    {
        return bcsub($a, $b, self::SCALA);
    }

    public static function per(string ...$fattori): string
    {
        $r = '1';
        foreach ($fattori as $f) {
            $r = bcmul($r, $f, self::SCALA);
        }

        return $r;
    }

    /** Come la divisione DECIMAL di MySQL: risultato arrotondato a (scala del dividendo + 4) cifre. */
    public static function diviso(string $a, string $b, int $scalaDividendo): string
    {
        return self::arrotonda(bcdiv($a, $b, self::SCALA), $scalaDividendo + 4);
    }

    /** Come ROUND(x, n) di MySQL su un DECIMAL. */
    public static function arrotonda(string $x, int $cifre): string
    {
        return bcround($x, $cifre);
    }

    public static function confronta(string $a, string $b): int
    {
        return bccomp($a, $b, self::SCALA);
    }

    public static function max(string $a, string $b): string
    {
        return self::confronta($a, $b) >= 0 ? $a : $b;
    }

    /**
     * Somma come SUM() di MySQL: ignora i NULL; se non resta nessun valore il risultato è NULL.
     *
     * @param  array<int, string|null>  $valori
     */
    public static function sommaSql(array $valori): ?string
    {
        $validi = array_values(array_filter($valori, fn ($v) => $v !== null));

        return $validi === [] ? null : self::somma(...$validi);
    }
}
