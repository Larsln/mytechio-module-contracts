<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Input of `Orders::cancel()`: what happens to the material of the order.
 * `mode` is `full_return` (all material is back), `consumed` (all material was
 * used up) or `mixed`; `reason` is mandatory for the audit log (at least 10
 * characters). `itemModes` is only read for `mixed` and names the outcome per
 * line, keyed by `OrderItemRef::$id`: `full_return` or `consumed` (a line
 * without an entry counts as returned).
 */
final readonly class OrderCancellation
{
    /**
     * @param  array<int, string>  $itemModes
     */
    public function __construct(
        public string $mode,
        public string $reason,
        public array $itemModes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'reason' => $this->reason,
            'item_modes' => $this->itemModes,
        ];
    }
}
