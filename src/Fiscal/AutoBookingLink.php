<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Binds an account to a scenario. For an outgoing scenario the account is the
 * automatic revenue account the bookings use for the scenario, for one tax
 * category or, with a null category, as the catch-all of every category
 * without a specific link.
 *
 * For an incoming scenario code the account is an expense account bound to
 * that scenario: it is usable for the scenario only (and the scenario proposes
 * it), and the category, when set, fixes its tax rate. Incoming scenario codes
 * and outgoing ones share one namespace per account, an account carries at
 * most one scenario.
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
