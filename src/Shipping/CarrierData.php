<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A shipping service staff can send with. `createsLabels` says that the
 * holder creates the label (and the tracking number) through the carrier's
 * interface; without it staff types the tracking number in.
 */
final readonly class CarrierData
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $createsLabels = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'creates_labels' => $this->createsLabels,
        ];
    }
}
