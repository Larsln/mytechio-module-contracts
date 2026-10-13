<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * Format rules shared by the statement DTOs: dates are `YYYY-MM-DD`, amounts
 * are decimal strings with exactly four fractional digits and an optional
 * leading minus.
 *
 * @internal
 */
final class StatementFormat
{
    /**
     * @throws StatementParseException
     */
    public static function assertDate(?string $value, string $field): void
    {
        if ($value === null) {
            return;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new StatementParseException(sprintf('Ungültiges Datum in "%s": %s', $field, $value));
        }
    }

    /**
     * @throws StatementParseException
     */
    public static function assertAmount(?string $value, string $field): void
    {
        if ($value === null) {
            return;
        }

        if (preg_match('/^-?\d+\.\d{4}$/', $value) !== 1) {
            throw new StatementParseException(sprintf('Ungültiger Betrag in "%s": %s', $field, $value));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
