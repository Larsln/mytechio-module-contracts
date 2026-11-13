<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * Where a shipment goes. `name` is the person or the company, `additional`
 * a second name line (a person at a company, c/o); `street` carries the
 * house number, `country` is ISO 3166-1 alpha-2. `email` and `phone` are
 * only passed on to the carrier when it needs them to announce the parcel.
 *
 * Since 2.20 a shipment can go to a pickup point of the carrier instead of
 * a house: `pickupType` is `locker` (a parcel locker such as a Packstation)
 * or `postoffice`, `pickupId` the number of that point, and `postNumber`
 * the customer's number with the carrier where the point needs one.
 * `street` then holds the point's own address for people to read; the
 * carrier is addressed by type and number.
 */
final readonly class ShipmentAddress
{
    public function __construct(
        public string $name,
        public string $street,
        public string $postalCode,
        public string $city,
        public string $country,
        public ?string $additional = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $pickupType = null,
        public ?string $pickupId = null,
        public ?string $postNumber = null,
    ) {}

    /** The parcel goes to a locker or a post office instead of a house. */
    public function isPickupPoint(): bool
    {
        return $this->pickupType !== null && $this->pickupId !== null && trim($this->pickupId) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'additional' => $this->additional,
            'street' => $this->street,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'pickup_type' => $this->pickupType,
            'pickup_id' => $this->pickupId,
            'post_number' => $this->postNumber,
        ];
    }
}
