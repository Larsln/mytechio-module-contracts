<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * A goods receipt for `Stock::recordReceipt()`. `quantity` is a positive
 * decimal string; `unitCostBase` is the goods price per unit, `unitIncidentalCost`
 * the apportioned incidental acquisition cost per unit (both net, 4 decimals,
 * `null` when unknown). `occurredOn` is the economic date (`Y-m-d`, `null` =
 * today), `reference` the document the receipt comes from.
 */
final readonly class NewStockReceipt
{
    public function __construct(
        public int $articleId,
        public string $quantity,
        public ?string $unitCostBase = null,
        public ?string $unitIncidentalCost = null,
        public ?string $occurredOn = null,
        public ?string $reason = null,
        public ?StockReference $reference = null,
    ) {
        Validate::decimal($quantity, 'quantity');

        foreach (['unitCostBase' => $unitCostBase, 'unitIncidentalCost' => $unitIncidentalCost] as $field => $value) {
            if ($value !== null) {
                Validate::decimal($value, $field);
            }
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
            'unit_cost_base' => $this->unitCostBase,
            'unit_incidental_cost' => $this->unitIncidentalCost,
            'occurred_on' => $this->occurredOn,
            'reason' => $this->reason,
            'reference' => $this->reference?->toArray(),
        ];
    }
}
