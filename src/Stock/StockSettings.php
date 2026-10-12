<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * Stock settings of an article for `Stock::updateSettings()`. `tracked` turns
 * stock tracking on or off (`null` leaves it unchanged); `reorderPoint` is the
 * threshold (decimal string, `null` clears it) and is required while
 * `lowStockWarningEnabled` is on.
 */
final readonly class StockSettings
{
    public function __construct(
        public bool $lowStockWarningEnabled,
        public ?string $reorderPoint = null,
        public ?bool $tracked = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'low_stock_warning_enabled' => $this->lowStockWarningEnabled,
            'reorder_point' => $this->reorderPoint,
            'tracked' => $this->tracked,
        ];
    }
}
