<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * Cost of goods sold of one article in one month, from the cost snapshots on
 * the outgoing sales movements. `month` is `YYYY-MM`, `quantity` the units
 * sold (positive) and `cogs` their cost (base + incidental share) with 4
 * decimal places. Only the sales movements count: the counter-movement of a
 * cancelled direct invoice is not netted against them.
 */
final readonly class CogsRow
{
    public function __construct(
        public int $articleId,
        public string $month,
        public string $quantity,
        public string $cogs,
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::decimal($cogs, 'cogs');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'month' => $this->month,
            'quantity' => $this->quantity,
            'cogs' => $this->cogs,
        ];
    }
}
