<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * A carrier driver that can say where a parcel is (since 2.20). The
 * holder of `shipping` asks it for the parcels of the carriers it
 * `tracksCarrier()`, on a schedule and when staff asks, and never more
 * often than `trackingBudget()` allows.
 */
interface TracksParcels
{
    /**
     * Whether parcels of a carrier can be asked about right now: tracking is switched on, the access is there
     * and the carrier is one of this driver's.
     *
     * @param  string  $driverKey  the driver the carrier is set to
     * @param  string|null  $trackingUrlTemplate  the carrier's public tracking page; lets a driver also follow a
     *                                            carrier that is kept without its interface
     */
    public function tracksCarrier(string $driverKey, ?string $trackingUrlTemplate): bool;

    /** How many questions the carrier still takes today; 0 while tracking is off. */
    public function trackingBudget(): int;

    /**
     * Where the parcel is; null when the carrier does not know the number (yet).
     *
     * @throws ShipmentException when the carrier cannot be asked: no budget left, refused access, unreachable
     */
    public function trackingState(string $trackingNumber): ?TrackingState;
}
