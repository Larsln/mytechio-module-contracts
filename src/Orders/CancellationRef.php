<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * How an order was cancelled. `mode` is `full_return` (all material is back),
 * `consumed` (all material was used up) or `mixed`. `itemModes` names the
 * outcome for EVERY line, keyed by `OrderItemRef::$id`: `full_return` or
 * `consumed`, so a hook never has to resolve the mode itself.
 */
final readonly class CancellationRef
{
    public const FULL_RETURN = 'full_return';

    public const CONSUMED = 'consumed';

    public const MIXED = 'mixed';

    /**
     * @param  array<int, string>  $itemModes
     */
    public function __construct(
        public string $mode,
        public ?string $reason,
        public array $itemModes,
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
