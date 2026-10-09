<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Read model of one template item. `quantity` and `unitPriceNet` are
 * 4-decimal strings; `extras` is the invoice-item extension payload keyed by
 * module name.
 */
final readonly class RecurringItemSummary
{
    /**
     * @param  array<string, mixed>  $extras
     */
    public function __construct(
        public int $id,
        public string $description,
        public string $quantity,
        public string $unitPriceNet,
        public array $extras = [],
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::decimal($unitPriceNet, 'unitPriceNet');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price_net' => $this->unitPriceNet,
            'extras' => $this->extras,
        ];
    }
}
