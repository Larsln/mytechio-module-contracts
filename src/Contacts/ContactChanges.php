<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * The customer-facing fields of a contact as a module wants them stored
 * (since 2.15): the core replaces exactly these fields and keeps every other
 * piece of contact data (type, direction, payment terms, price list, notes,
 * billing profile, further contact persons) untouched. `null` clears an
 * optional field. For a `b2b` contact the names update its primary contact
 * person.
 */
final readonly class ContactChanges
{
    public function __construct(
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
