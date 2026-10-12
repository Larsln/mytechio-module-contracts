<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * A sale or customer return for `Stock::recordIssue()`. `quantity` is a
 * positive decimal string (the sign comes from the type). For a sale,
 * `unitCost` is the cost-of-goods snapshot per unit; `null` makes the
 * implementation take the article's last purchase price. Selling beyond the
 * stock is allowed (the stock goes negative); the implementation never blocks
 * a sale.
 */
final readonly class NewStockIssue
{
    public function __construct(
        public int $articleId,
        public string $quantity,
        public StockIssueType $type = StockIssueType::Sale,
        public ?string $unitCost = null,
        public ?string $occurredOn = null,
        public ?string $reason = null,
        public ?StockReference $reference = null,
    ) {
        Validate::decimal($quantity, 'quantity');

        if ($unitCost !== null) {
            Validate::decimal($unitCost, 'unitCost');
        }

        if ($occurredOn !== null) {
            Validate::date($occurredOn, 'occurredOn');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'quantity' => $this->quantity,
            'type' => $this->type->value,
            'unit_cost' => $this->unitCost,
            'occurred_on' => $this->occurredOn,
            'reason' => $this->reason,
            'reference' => $this->reference?->toArray(),
        ];
    }
}
