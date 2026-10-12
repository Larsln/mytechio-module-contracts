<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * One entry of the quantity ledger. `quantity` is signed (receipts positive,
 * outgoing movements negative); the unit costs are the purchase-price
 * snapshot of the movement (`unitCostAtMovement` = base + incidental share),
 * `null` when none was known. `occurredAt` is the economic date-time of the
 * transaction (ISO 8601), `voucherNumber` names the stock voucher the
 * movement belongs to.
 */
final readonly class StockMovementData
{
    public function __construct(
        public int $id,
        public int $articleId,
        public StockMovementType $type,
        public string $quantity,
        public ?string $referenceType,
        public ?int $referenceId,
        public ?string $voucherNumber,
        public ?string $reason,
        public ?string $unitCostAtMovement,
        public ?string $unitCostBase,
        public ?string $unitIncidentalCost,
        public ?int $bookedById,
        public string $occurredAt,
    ) {
        Validate::decimal($quantity, 'quantity');

        foreach (['unitCostAtMovement' => $unitCostAtMovement, 'unitCostBase' => $unitCostBase, 'unitIncidentalCost' => $unitIncidentalCost] as $field => $value) {
            if ($value !== null) {
                Validate::decimal($value, $field);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'type' => $this->type->value,
            'quantity' => $this->quantity,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'voucher_number' => $this->voucherNumber,
            'reason' => $this->reason,
            'unit_cost_at_movement' => $this->unitCostAtMovement,
            'unit_cost_base' => $this->unitCostBase,
            'unit_incidental_cost' => $this->unitIncidentalCost,
            'booked_by_id' => $this->bookedById,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
