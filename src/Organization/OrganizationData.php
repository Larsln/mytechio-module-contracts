<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Organization;

/**
 * Who the company is, as the core's organization settings say: the legal
 * name, the postal address (`street` carries the house number, `countryCode`
 * is ISO 3166-1 alpha-2) and how to reach it. For papers a module prints
 * itself (a delivery note, a return slip).
 */
final readonly class OrganizationData
{
    public function __construct(
        public string $legalName,
        public string $street,
        public string $postalCode,
        public string $city,
        public string $countryCode,
        public ?string $addressAddition = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?string $vatId = null,
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'legal_name' => $this->legalName,
            'street' => $this->street,
            'address_addition' => $this->addressAddition,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country_code' => $this->countryCode,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'vat_id' => $this->vatId,
        ];
    }
}
