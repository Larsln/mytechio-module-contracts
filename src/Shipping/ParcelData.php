<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A parcel of a shipment. `position` counts from 1. `trackingUrl` is the
 * carrier's tracking page for the parcel (null without a tracking number or
 * a link template); `hasLabel` says that `Shipments::label()` returns one.
 * `printState` is where the label stands at the holder's printer: `queued`,
 * `printed` or `failed`, and null when it was never sent to a printer.
 *
 * Since 2.20 the holder may follow the parcel at the carrier:
 * `trackingStatus` is `pre-transit`, `transit`, `delivered`, `failure` or
 * `unknown` (null while the carrier was never asked), `trackingText` the
 * carrier's own words for where the parcel is and `trackingAt` the moment
 * of that event (ISO 8601).
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
        public ?string $printState = null,
        public ?string $trackingStatus = null,
        public ?string $trackingText = null,
        public ?string $trackingAt = null,
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
            'print_state' => $this->printState,
            'tracking_status' => $this->trackingStatus,
            'tracking_text' => $this->trackingText,
            'tracking_at' => $this->trackingAt,
        ];
    }
}
