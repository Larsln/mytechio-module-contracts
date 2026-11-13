<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A shipment as its holder knows it. `carrierName` is the name at the time
 * of the shipment as staff knows the service; `carrierPublicName` (since
 * 2.20) is the name for customers when the holder keeps a separate one, and
 * `customerCarrierName()` is what to show a customer; `shippedAt` is ISO 8601, `deliveredOn` a date (`Y-m-d`)
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
        public ?string $carrierPublicName = null,
    ) {}

    /** The carrier's name for mails and pages a customer reads. */
    public function customerCarrierName(): string
    {
        return $this->carrierPublicName !== null && trim($this->carrierPublicName) !== '' ? $this->carrierPublicName : $this->carrierName;
    }

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
            'carrier_public_name' => $this->carrierPublicName,
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
