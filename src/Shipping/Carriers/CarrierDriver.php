<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * How shipments reach a carrier (since 2.20). A module that connects a
 * carrier's interface offers a driver; the holder of the capability
 * `shipping` finds every driver through the container tag {@see self::TAG}
 * and lets staff set a carrier to it by its `key()`:
 *
 *     $this->app->tag([MyDriver::class], CarrierDriver::TAG);
 *
 * A driver that `createsLabels()` books every parcel through the carrier's
 * interface and returns its tracking number and label; while it cannot
 * (no access data yet), the holder treats the carrier like one without an
 * interface. A driver may also implement {@see TracksParcels} and
 * {@see CreatesReturnLabels}.
 */
interface CarrierDriver
{
    public const string TAG = 'mytechio.shipping.carrier-drivers';

    /** Short stable key the holder stores with a carrier, e.g. `dhl`. Lower case letters, digits and `-`. */
    public function key(): string;

    /** German name for the carrier form. */
    public function label(): string;

    /** Whether the driver can create labels right now (its access is complete). */
    public function createsLabels(): bool;

    /**
     * Books the parcels of a shipment that passed the holder's general checks.
     *
     * @return list<DriverParcel> one per parcel, in the order of `$shipment->parcels`
     *
     * @throws ShipmentException when the carrier refuses or cannot be reached; nothing stays booked then
     */
    public function createShipment(NewShipment $shipment): array;

    /**
     * Cancels what `createShipment()` booked.
     *
     * @param  list<string>  $references  the carrier's shipment numbers (`DriverParcel::$reference`)
     *
     * @throws ShipmentException when the carrier refuses or cannot be reached
     */
    public function cancelShipment(array $references): void;
}
