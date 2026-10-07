<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * A single line of a journal entry draft.
 *
 * `amount` is always positive (a string with 4 decimal places, as for
 * all monetary amounts in this package) — debit/credit is expressed
 * exclusively via `side`, never via a sign.
 */
final readonly class JournalLineDraft
{
    public function __construct(
        public string $accountNumber,
        public Side $side,
        public string $amount,
        public ?string $taxCode = null,
        public ?string $memo = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'side' => $this->side->value,
            'amount' => $this->amount,
            'tax_code' => $this->taxCode,
            'memo' => $this->memo,
        ];
    }
}
