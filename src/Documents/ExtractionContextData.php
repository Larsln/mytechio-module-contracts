<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Context for `InvoiceExtractor::extract()`: the active expense accounts for
 * account suggestions, as well as the canonical list of units, so the model
 * maps printed units to standard codes (free text only as a fallback).
 * Deliberately WITHOUT a contact list — supplier matching runs in the core,
 * never in the model.
 */
final readonly class ExtractionContextData
{
    /**
     * @param  list<array{number: string, name: string, category: ?string}>  $expenseAccounts
     * @param  list<string>  $units
     */
    public function __construct(
        public array $expenseAccounts,
        public array $units,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expense_accounts' => $this->expenseAccounts,
            'units' => $this->units,
        ];
    }
}
