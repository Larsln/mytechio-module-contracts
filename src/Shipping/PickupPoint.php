<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * A place of the carrier where a customer can have a parcel delivered: a
 * parcel locker (`locker`, e.g. a Packstation) or a post office
 * (`postoffice`). `id` is the number the carrier addresses it by, `label`
 * what people call it ("Packstation 130"), `distanceMeters` the distance
 * from the address searched for.
 */
final readonly class PickupPoint
{
    public const string LOCKER = 'locker';

    public const string POST_OFFICE = 'postoffice';

    public function __construct(
        public string $type,
        public string $id,
        public string $label,
        public string $street,
        public string $postalCode,
        public string $city,
        public string $country,
        public ?int $distanceMeters = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'label' => $this->label,
            'street' => $this->street,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
            'distance_meters' => $this->distanceMeters,
        ];
    }
}
