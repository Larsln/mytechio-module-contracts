<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * One parcel of a new shipment: the packed weight in grams, the outer
 * measures in millimetres when known and, for a carrier without an
 * interface, the tracking number staff typed in.
 */
final readonly class NewParcel
{
    public function __construct(
        public int $weightGrams,
        public ?int $lengthMm = null,
        public ?int $widthMm = null,
        public ?int $heightMm = null,
        public ?string $trackingNumber = null,
        public ?string $packaging = null,
    ) {}
}
