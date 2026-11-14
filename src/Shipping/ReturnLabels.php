<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Return labels for shipments (since 2.20), offered by the holder of the
 * capability `shipping` when it can create them. The holder keeps a price
 * per country; a country without a price gets no label. A label is made for
 * the way back of one shipment: the recipient of the shipment sends, the
 * holder's return address receives.
 *
 * Who pays the price and how (deducting it from a refund, for instance) is
 * the caller's business; this contract books nothing.
 *
 * Not every shipping module offers it: resolve it optionally.
 */
interface ReturnLabels
{
    /** The price of a return label from a country; null when none is offered there. */
    public function offer(string $country): ?ReturnOffer;

    /**
     * @return array<string, ReturnOffer> by country, every country a label is offered for
     */
    public function offers(): array;

    /**
     * Creates a return label for a shipment that left the house.
     *
     * @throws ShipmentException when the shipment is unknown, cancelled or not shipped yet, no label is offered
     *                           for its country, or the carrier refuses or cannot be reached
     */
    public function create(int $shipmentId, ActorRef $actor): ReturnLabelData;

    /**
     * @return list<ReturnLabelData> the return labels of a shipment, oldest first
     */
    public function forShipment(int $shipmentId): array;

    /** The file of a return label; null when it is unknown or already deleted. */
    public function label(int $returnId): ?ShipmentLabel;
}
