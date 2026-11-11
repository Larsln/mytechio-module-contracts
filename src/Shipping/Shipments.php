<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Shipments of orders (since 2.19), an exclusive capability (`shipping`) a
 * shipping module holds. The holder knows the shipping services, which one
 * is the default per country, creates labels where a service has an
 * interface and keeps the state of every shipment. What a shipment means
 * for the order (completing it, the invoice, mails) is the caller's
 * business; this contract moves no stock and writes no documents.
 *
 * Without a holder the contract is not bound: resolve it optionally.
 */
interface Shipments
{
    /**
     * The shipping services staff can send with, in the holder's order.
     *
     * @return list<CarrierData>
     */
    public function carriers(): array;

    /** The service preselected for a country (ISO 3166-1 alpha-2); null when none is set. */
    public function carrierFor(string $country): ?CarrierData;

    /**
     * Creates the shipment of an order with its parcels. A service with an
     * interface creates the labels and tracking numbers now; for any other
     * the parcels carry the tracking numbers staff typed in (or none). An
     * order has one shipment that is not cancelled.
     *
     * @throws ShipmentException when the carrier is unknown, the order already has a shipment, the parcels are
     *                           invalid or the carrier refuses or cannot be reached
     */
    public function create(NewShipment $shipment, ActorRef $actor): ShipmentData;

    public function find(int $shipmentId): ?ShipmentData;

    /** The shipment of the order that is not cancelled. */
    public function forOrder(int $orderId): ?ShipmentData;

    /**
     * @param  list<int>  $orderIds
     * @return array<int, ShipmentData> by order id; orders without a shipment are missing
     */
    public function forOrders(array $orderIds): array;

    /** The label of a parcel (position from 1); null when the shipment has none. */
    public function label(int $shipmentId, int $position): ?ShipmentLabel;

    /**
     * Notes that the shipment left the house. Repeating it changes nothing.
     *
     * @throws ShipmentException when the shipment is unknown or cancelled
     */
    public function markShipped(int $shipmentId, ActorRef $actor): ShipmentData;

    /**
     * Notes the delivery: the day (`Y-m-d`) and what proves it. A shipment
     * that was not marked as shipped counts as shipped from then on.
     *
     * @throws ShipmentException when the shipment is unknown or cancelled, or the day is no date or in the future
     */
    public function markDelivered(int $shipmentId, string $deliveredOn, ?string $note, ActorRef $actor): ShipmentData;

    /**
     * Cancels a shipment that did not leave the house; a label created through an interface is cancelled with
     * the carrier.
     *
     * @throws ShipmentException when the shipment is unknown, already on its way or the carrier refuses
     */
    public function cancel(int $shipmentId, ActorRef $actor): ShipmentData;
}
