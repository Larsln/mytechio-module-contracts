<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * Decimal arithmetic on strings the way the core does it for documents:
 * four decimals per step, rounded half to even (banker's rounding). Modules
 * that have to reproduce a document amount (e.g. to back-calculate a net
 * unit price from an end price) use these instead of floats.
 */
final class Decimal
{
    /** Rounds a decimal string to `$scale` places, half to even. */
    public static function round(string $value, int $scale): string
    {
        $negative = str_starts_with($value, '-');
        $absolute = ltrim($value, '+-');
        [$whole, $fraction] = explode('.', $absolute.'.') + [1 => ''];
        $whole = $whole === '' ? '0' : $whole;
        $kept = substr(str_pad($fraction, $scale, '0'), 0, $scale);
        $rest = rtrim((string) substr($fraction, $scale), '0');
        $units = ltrim($whole.$kept, '0');
        $units = $units === '' ? '0' : $units;

        if ($rest !== '') {
            $half = bccomp('0.'.$rest, '0.5', strlen($rest) + 1);

            if ($half > 0 || ($half === 0 && ((int) substr($units, -1)) % 2 === 1)) {
                $units = bcadd($units, '1', 0);
            }
        }

        $rounded = $scale === 0 ? $units : bcdiv($units, bcpow('10', (string) $scale, 0), $scale);

        return $negative && bccomp($rounded, '0', $scale) !== 0 ? '-'.$rounded : $rounded;
    }

    /** `$a × $b`, rounded half to even to `$scale` places. */
    public static function multiply(string $a, string $b, int $scale = 4): string
    {
        return self::round(bcmul($a, $b, self::places($a) + self::places($b)), $scale);
    }

    /**
     * The amounts of a document line as the core computes them: net =
     * quantity × unit price − line discount, tax = net × rate, each step at
     * four decimals half to even.
     *
     * @return array{net: string, tax: string, gross: string} four decimals each
     */
    public static function line(string $quantity, string $unitPriceNet, string $taxRate, string $discountPercent = '0'): array
    {
        $subtotal = self::multiply($unitPriceNet, $quantity);
        $discount = self::multiply($subtotal, self::round(bcdiv($discountPercent, '100', 8), 6));
        $net = bcsub($subtotal, $discount, 4);
        $tax = self::multiply($net, self::round(bcdiv($taxRate, '100', 8), 6));

        return ['net' => $net, 'tax' => $tax, 'gross' => bcadd($net, $tax, 4)];
    }

    private static function places(string $value): int
    {
        $dot = strpos($value, '.');

        return $dot === false ? 0 : strlen($value) - $dot - 1;
    }
}
