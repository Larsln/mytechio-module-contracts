<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

use MyTechIO\Contracts\Shipping\PickupPoint;
use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * A source of pickup points (since 2.20): a module that connects a
 * carrier and can both find its parcel lockers and post offices and send
 * to them offers one through the container tag {@see self::TAG}. The
 * module that lets customers choose a pickup point (`PickupPoints`)
 * gathers every provider; other modules use `PickupPoints`, not this.
 */
interface PickupPointProvider
{
    public const string TAG = 'mytechio.shipping.pickup-point-providers';

    /** German name of the carrier, for staff. */
    public function carrier(): string;

    /**
     * Countries (ISO 3166-1 alpha-2) the provider can find pickup points in and send to right now; empty while
     * its access is missing.
     *
     * @return list<string>
     */
    public function countries(): array;

    /**
     * The pickup points near a postcode, nearest first.
     *
     * @return list<PickupPoint>
     *
     * @throws ShipmentException when the carrier cannot be asked right now
     */
    public function near(string $country, string $postalCode, ?string $city = null, ?string $street = null): array;

    /**
     * One pickup point by type and number at a postcode; null when the carrier knows none like it there.
     *
     * @throws ShipmentException when the carrier cannot be asked right now
     */
    public function find(string $country, string $postalCode, string $type, string $id): ?PickupPoint;

    /** Whether a customer number with the carrier looks like one (the carrier's own format; no lookup). */
    public function validPostNumber(string $postNumber): bool;
}
