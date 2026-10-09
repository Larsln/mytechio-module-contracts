<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\ContractException;

/**
 * Argument validation shared by the recurring-invoice DTOs.
 *
 * @internal
 */
final class Validate
{
    public static function decimal(string $value, string $field): void
    {
        if (preg_match('/^-?\d+(\.\d{1,4})?$/', $value) !== 1) {
            throw new ContractException("{$field} must be a decimal string with up to 4 decimals, got \"{$value}\".");
        }
    }

    public static function date(string $value, string $field): void
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($parsed === false || $parsed->format('Y-m-d') !== $value) {
            throw new ContractException("{$field} must be a date formatted YYYY-MM-DD, got \"{$value}\".");
        }
    }

    public static function interval(string $unit, int $count): void
    {
        if (! in_array($unit, ['month', 'year'], true)) {
            throw new ContractException("intervalUnit must be 'month' or 'year', got \"{$unit}\".");
        }

        if ($count < 1) {
            throw new ContractException('intervalCount must be at least 1.');
        }
    }
}
