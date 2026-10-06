<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Eine Zeile eines Buchungssatz-Entwurfs.
 *
 * `amount` ist immer positiv (String mit 4 Nachkommastellen, wie bei allen
 * Geldbeträgen in diesem Paket) — Soll/Haben wird ausschließlich über
 * `side` ausgedrückt, niemals über ein Vorzeichen.
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
