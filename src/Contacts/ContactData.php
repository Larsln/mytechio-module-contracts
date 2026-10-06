<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * Schreibgeschützte Sicht auf einen Kontakt des Kerns.
 *
 * `type` unterscheidet z. B. `'company'`/`'person'`, `direction`
 * `'customer'`/`'supplier'`/`'both'` — die Implementierung legt die genaue
 * Wertemenge fest, Module behandeln beide Felder als Freitext.
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
        /** Seit 1.3.0 (Nextcloud-Modul): Mobilnummer, Website, letzte Änderung (ISO-8601). */
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
