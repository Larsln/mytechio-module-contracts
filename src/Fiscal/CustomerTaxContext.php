<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The customer side of the place-of-supply rules, resolved by the core from
 * the contact: region and status, the customer's country, and the two contact
 * facts a description may mention (a business contact whose status did not
 * qualify, a small-business contact).
 */
final readonly class CustomerTaxContext
{
    public function __construct(
        public CustomerRegion $region,
        public CustomerStatus $status,
        public string $countryCode,
        public bool $isBusinessContact = false,
        public bool $isSmallBusiness = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'region' => $this->region->value,
            'status' => $this->status->value,
            'country_code' => $this->countryCode,
            'is_business_contact' => $this->isBusinessContact,
            'is_small_business' => $this->isSmallBusiness,
        ];
    }
}
