<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * Pickup points of a carrier (since 2.20): parcel lockers and post offices
 * a customer can choose instead of a house address. Offered by a shipping
 * module that can both find them and send to them; a shipment goes there
 * through `ShipmentAddress::$pickupType`, `$pickupId` and `$postNumber`.
 *
 * Not every shipping module offers it: resolve it optionally.
 */
interface PickupPoints
{
    /** Countries (ISO 3166-1 alpha-2) the holder can deliver to a pickup point in; empty when it cannot at all. */
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
     * One pickup point by type and number at a postcode, to check what a customer chose; null when the carrier
     * knows none like it there.
     *
     * @throws ShipmentException when the carrier cannot be asked right now
     */
    public function find(string $country, string $postalCode, string $type, string $id): ?PickupPoint;

    /** Whether a customer number with the carrier looks like one (the carrier's own format; no lookup). */
    public function validPostNumber(string $postNumber): bool;
}
