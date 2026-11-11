<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A shipment to create: the order it belongs to, the carrier, the recipient
 * and the parcels (at least one). `reference` is printed on the label where
 * the carrier allows it (e.g. the order number).
 */
final readonly class NewShipment
{
    /**
     * @param  list<NewParcel>  $parcels
     */
    public function __construct(
        public int $orderId,
        public int $carrierId,
        public ShipmentAddress $recipient,
        public array $parcels,
        public ?string $reference = null,
    ) {}
}
