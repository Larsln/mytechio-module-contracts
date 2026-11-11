<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A parcel of a shipment. `position` counts from 1. `trackingUrl` is the
 * carrier's tracking page for the parcel (null without a tracking number or
 * a link template); `hasLabel` says that `Shipments::label()` returns one.
 */
final readonly class ParcelData
{
    public function __construct(
        public int $position,
        public int $weightGrams,
        public ?int $lengthMm = null,
        public ?int $widthMm = null,
        public ?int $heightMm = null,
        public ?string $trackingNumber = null,
        public ?string $trackingUrl = null,
        public bool $hasLabel = false,
        public ?string $packaging = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position,
            'weight_grams' => $this->weightGrams,
            'length_mm' => $this->lengthMm,
            'width_mm' => $this->widthMm,
            'height_mm' => $this->heightMm,
            'tracking_number' => $this->trackingNumber,
            'tracking_url' => $this->trackingUrl,
            'has_label' => $this->hasLabel,
            'packaging' => $this->packaging,
        ];
    }
}
