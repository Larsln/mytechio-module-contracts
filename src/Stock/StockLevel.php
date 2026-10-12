<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * Stock position of one article. Quantities are decimal strings with 4
 * decimals; an article that is not stock-tracked has `tracked = false` and
 * all quantities `"0.0000"`. `available` is `onHand - reserved`; `isLow` is
 * true when the article opted into the low-stock warning and `available` is at
 * or below `reorderPoint`.
 */
final readonly class StockLevel
{
    public function __construct(
        public int $articleId,
        public bool $tracked,
        public string $onHand,
        public string $reserved,
        public string $available,
        public ?string $reorderPoint,
        public bool $lowStockWarningEnabled,
        public bool $isLow,
    ) {
        Validate::decimal($onHand, 'onHand');
        Validate::decimal($reserved, 'reserved');
        Validate::decimal($available, 'available');

        if ($reorderPoint !== null) {
            Validate::decimal($reorderPoint, 'reorderPoint');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'tracked' => $this->tracked,
            'on_hand' => $this->onHand,
            'reserved' => $this->reserved,
            'available' => $this->available,
            'reorder_point' => $this->reorderPoint,
            'low_stock_warning_enabled' => $this->lowStockWarningEnabled,
            'is_low' => $this->isLow,
        ];
    }
}
