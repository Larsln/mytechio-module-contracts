<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A shipment as its holder knows it. `carrierName` is the name at the time
 * of the shipment; `shippedAt` is ISO 8601, `deliveredOn` a date (`Y-m-d`)
 * and `deliveryNote` what staff noted as proof of delivery.
 */
final readonly class ShipmentData
{
    /**
     * @param  list<ParcelData>  $parcels
     */
    public function __construct(
        public int $id,
        public int $orderId,
        public int $carrierId,
        public string $carrierName,
        public ShipmentStatus $status,
        public ShipmentAddress $recipient,
        public array $parcels,
        public ?string $shippedAt = null,
        public ?string $deliveredOn = null,
        public ?string $deliveryNote = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'carrier_id' => $this->carrierId,
            'carrier_name' => $this->carrierName,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'recipient' => $this->recipient->toArray(),
            'parcels' => array_map(static fn (ParcelData $parcel): array => $parcel->toArray(), $this->parcels),
            'shipped_at' => $this->shippedAt,
            'delivered_on' => $this->deliveredOn,
            'delivery_note' => $this->deliveryNote,
        ];
    }
}
