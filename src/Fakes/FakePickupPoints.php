<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Shipping\PickupPoint;
use MyTechIO\Contracts\Shipping\PickupPoints;
use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * Test double for `PickupPoints`: `seed()` puts a point at a postcode,
 * `failNext()` plays a carrier that cannot be asked, `searches()` lists
 * what was asked for. Post numbers are 6 to 10 digits, like DHL's.
 */
final class FakePickupPoints implements PickupPoints
{
    /** @var list<PickupPoint> */
    private array $points = [];

    /** @var list<array{country: string, postal_code: string, city: string|null, street: string|null}> */
    private array $searches = [];

    private ?string $failure = null;

    /** @var list<string> */
    private array $countries = ['DE'];

    public function seed(PickupPoint $point): void
    {
        $this->points[] = $point;
    }

    /**
     * @param  list<string>  $countries
     */
    public function deliverIn(array $countries): void
    {
        $this->countries = array_map('strtoupper', $countries);
    }

    public function countries(): array
    {
        return $this->countries;
    }

    public function near(string $country, string $postalCode, ?string $city = null, ?string $street = null): array
    {
        $this->failIfAsked();
        $this->searches[] = ['country' => strtoupper($country), 'postal_code' => $postalCode, 'city' => $city, 'street' => $street];

        $found = array_values(array_filter(
            $this->points,
            static fn (PickupPoint $point): bool => $point->country === strtoupper($country) && $point->postalCode === $postalCode,
        ));
        usort($found, static fn (PickupPoint $a, PickupPoint $b): int => ($a->distanceMeters ?? PHP_INT_MAX) <=> ($b->distanceMeters ?? PHP_INT_MAX));

        return $found;
    }

    public function find(string $country, string $postalCode, string $type, string $id): ?PickupPoint
    {
        $this->failIfAsked();

        foreach ($this->points as $point) {
            if ($point->country === strtoupper($country) && $point->postalCode === $postalCode && $point->type === $type && $point->id === $id) {
                return $point;
            }
        }

        return null;
    }

    public function validPostNumber(string $postNumber): bool
    {
        return preg_match('/^\d{6,10}$/', $postNumber) === 1;
    }

    /** The next search or lookup fails with this message. */
    public function failNext(string $message = 'Der Paketdienst ist nicht erreichbar.'): void
    {
        $this->failure = $message;
    }

    /**
     * @return list<array{country: string, postal_code: string, city: string|null, street: string|null}>
     */
    public function searches(): array
    {
        return $this->searches;
    }

    private function failIfAsked(): void
    {
        if ($this->failure !== null) {
            $message = $this->failure;
            $this->failure = null;

            throw new ShipmentException($message);
        }
    }
}
