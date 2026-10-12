<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Marks an account as the automatic account of an outgoing scenario: the
 * revenue account bookings use for the scenario, for one tax category or, with
 * a null category, as the catch-all of every category without a specific link.
 */
final readonly class AutoBookingLink
{
    public function __construct(
        public string $accountNumber,
        public string $scenario,
        public ?string $taxCategoryCode = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'scenario' => $this->scenario,
            'tax_category_code' => $this->taxCategoryCode,
        ];
    }
}
