<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * What a module hands in to create a customer contact (since 2.15): the
 * customer-facing master data only. `type` is `b2c` or `b2b`; a `b2c`
 * contact is the person itself (salutation, first and last name), a `b2b`
 * contact is the company and the names become its first contact person.
 * The direction is always `customer`; everything else (payment terms,
 * price lists, notes, billing profile) stays with the core.
 */
final readonly class NewContact
{
    public function __construct(
        public string $type,
        public ?string $salutation,
        public ?string $companyName,
        public ?string $firstName,
        public ?string $lastName,
        public string $email,
        public ?string $phone,
        public string $street,
        public string $houseNumber,
        public ?string $addressAddition,
        public string $postalCode,
        public string $city,
        public string $countryCode,
        public ?string $vatId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'salutation' => $this->salutation,
            'company_name' => $this->companyName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'house_number' => $this->houseNumber,
            'address_addition' => $this->addressAddition,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country_code' => $this->countryCode,
            'vat_id' => $this->vatId,
        ];
    }
}
