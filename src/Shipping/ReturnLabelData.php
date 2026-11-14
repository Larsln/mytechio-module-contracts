<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A return label created for a shipment: the carrier's number for the way
 * back, the price the customer was quoted for it at that moment (decimal
 * string, two places) and when it was created (ISO 8601). `hasLabel` turns
 * false once the holder deleted the file.
 */
final readonly class ReturnLabelData
{
    public function __construct(
        public int $id,
        public int $shipmentId,
        public int $orderId,
        public string $country,
        public ?string $trackingNumber,
        public ?string $trackingUrl,
        public string $price,
        public string $createdAt,
        public bool $hasLabel = true,
        public string $currency = 'EUR',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shipment_id' => $this->shipmentId,
            'order_id' => $this->orderId,
            'country' => $this->country,
            'tracking_number' => $this->trackingNumber,
            'tracking_url' => $this->trackingUrl,
            'price' => $this->price,
            'currency' => $this->currency,
            'created_at' => $this->createdAt,
            'has_label' => $this->hasLabel,
        ];
    }
}
