<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * A stocktake for `Stock::inventoryCount()`: `countedQuantity` is the
 * physically counted stock (not a difference), `reason` is mandatory (GoBD),
 * `voucherDate` (`Y-m-d`, not in the future; `null` = today) dates the
 * voucher.
 */
final readonly class NewInventoryCount
{
    public function __construct(
        public int $articleId,
        public string $countedQuantity,
        public string $reason,
        public ?string $voucherDate = null,
    ) {
        Validate::decimal($countedQuantity, 'countedQuantity');

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
            'counted_quantity' => $this->countedQuantity,
            'reason' => $this->reason,
            'voucher_date' => $this->voucherDate,
        ];
    }
}
