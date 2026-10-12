<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * A manual write-off for `Stock::writeOff()`: `type` is one of
 * `StockMovementType::writeOffCases()`, `quantity` the positive amount to
 * remove, `reason` is mandatory (GoBD). `voucherDate` (`Y-m-d`, not in the
 * future; `null` = today) dates the voucher.
 */
final readonly class NewWriteOff
{
    public function __construct(
        public int $articleId,
        public StockMovementType $type,
        public string $quantity,
        public string $reason,
        public ?string $voucherDate = null,
    ) {
        Validate::decimal($quantity, 'quantity');

        if ($voucherDate !== null) {
            Validate::date($voucherDate, 'voucherDate');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'type' => $this->type->value,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'voucher_date' => $this->voucherDate,
        ];
    }
}
