<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * Where a shipment goes. `name` is the person or the company, `additional`
 * a second name line (a person at a company, c/o); `street` carries the
 * house number, `country` is ISO 3166-1 alpha-2. `email` and `phone` are
 * only passed on to the carrier when it needs them to announce the parcel.
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
    ) {}

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
        ];
    }
}
