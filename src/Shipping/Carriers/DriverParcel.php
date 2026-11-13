<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

/**
 * What a driver knows about a parcel after booking it (since 2.20): the
 * tracking number, the carrier's own shipment number (to cancel it) and
 * the label as base64 with its MIME type. `labelFormat` names the paper
 * the label is made for (e.g. `A5`), so a label printer can pick the tray;
 * null when the driver does not know.
 */
final readonly class DriverParcel
{
    public function __construct(
        public ?string $trackingNumber = null,
        public ?string $reference = null,
        public ?string $labelBase64 = null,
        public ?string $labelMime = null,
        public ?string $labelFormat = null,
    ) {}
}
