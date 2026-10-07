<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * Read-only view of a core contact.
 *
 * `type` distinguishes e.g. `'company'`/`'person'`, `direction`
 * `'customer'`/`'supplier'`/`'both'` — the implementation defines the exact
 * set of values; modules treat both fields as free text.
 */
final readonly class ContactData
{
    public function __construct(
        public int $id,
        public string $displayName,
        public string $type,
        public string $direction,
        public ?string $companyName,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public ?string $street,
        public ?string $houseNumber,
        public ?string $addressAddition,
        public ?string $postalCode,
        public ?string $city,
        public ?string $countryCode,
        public ?string $vatId,
        /** Since 1.3.0 (Nextcloud module): mobile number, website, last changed (ISO-8601). */
        public ?string $mobile = null,
        public ?string $website = null,
        public ?string $updatedAt = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->displayName,
            'type' => $this->type,
            'direction' => $this->direction,
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
            'mobile' => $this->mobile,
            'website' => $this->website,
            'updated_at' => $this->updatedAt,
        ];
    }
}
