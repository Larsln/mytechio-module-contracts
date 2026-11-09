<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Contacts\ContactChanges;
use MyTechIO\Contracts\Contacts\ContactData;
use MyTechIO\Contracts\Contacts\ContactNotFoundException;
use MyTechIO\Contracts\Contacts\Contacts;
use MyTechIO\Contracts\Contacts\ContactValidationException;
use MyTechIO\Contracts\Contacts\NewContact;

/**
 * Test double for `Contacts`: in-memory store, `seed()` sets the initial
 * state. `search()` filters simply by substring (case-insensitive) over
 * `displayName` — sufficient for module tests, no relevance ranking like in
 * the core. `suppliers()` returns the seeded contacts with direction
 * `supplier`/`both`, ordered by display name. `create()`/`update()` apply
 * the core's required-field rules (type, email, names or company, address,
 * two-letter country, VAT ID only for `b2b` with the country prefix),
 * assign ascending ids and customer numbers, and record the actor of every
 * write in `actors()`; `vatIdValidity()` sets what a VAT ID is reported as.
 */
final class FakeContacts implements Contacts
{
    /**
     * @var array<int, ContactData>
     */
    private array $contacts = [];

    /**
     * @var array<string, string>
     */
    private array $countries = [
        'DE' => 'Deutschland',
        'AT' => 'Österreich',
        'CH' => 'Schweiz',
    ];

    /** @var array<string, bool|null> normalized VAT ID ⇒ validity, null = not checkable */
    private array $vatIds = [];

    /** @var list<array{action: string, contact_id: int, actor: ActorRef}> */
    private array $actors = [];

    private int $sequence = 0;

    public function seed(ContactData $contact): void
    {
        $this->contacts[$contact->id] = $contact;
        $this->sequence = max($this->sequence, $contact->id);
    }

    /** Declares how a VAT ID (any spacing or case) is answered by the VIES check: `null` = service unavailable. */
    public function vatIdValidity(string $vatId, ?bool $valid): void
    {
        $this->vatIds[self::normalizeVatId($vatId)] = $valid;
    }

    public function find(int $id): ?ContactData
    {
        return $this->contacts[$id] ?? null;
    }

    public function findMany(array $ids): array
    {
        return array_intersect_key($this->contacts, array_flip($ids));
    }

    public function search(string $query, int $limit = 20): array
    {
        $needle = mb_strtolower($query);

        $matches = array_values(array_filter(
            $this->contacts,
            static fn (ContactData $contact): bool => str_contains(mb_strtolower($contact->displayName), $needle),
        ));

        return array_slice($matches, 0, $limit);
    }

    public function suppliers(): array
    {
        $suppliers = array_values(array_filter(
            $this->contacts,
            static fn (ContactData $contact): bool => in_array($contact->direction, ['supplier', 'both'], true),
        ));

        usort($suppliers, static fn (ContactData $a, ContactData $b): int => strcmp(mb_strtolower($a->displayName), mb_strtolower($b->displayName)));

        return $suppliers;
    }

    public function countries(): array
    {
        return $this->countries;
    }

    /**
     * @param  array<string, string>  $countries  ISO-2 ⇒ country name (German)
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }

    public function findByEmail(string $email): array
    {
        $needle = mb_strtolower(trim($email));

        return array_values(array_filter(
            $this->contacts,
            static fn (ContactData $contact): bool => $contact->email !== null && mb_strtolower($contact->email) === $needle,
        ));
    }

    public function create(NewContact $contact, ActorRef $actor): ContactData
    {
        if (! in_array($contact->type, ['b2b', 'b2c'], true)) {
            throw ContactValidationException::forField('type', 'Der Kontakttyp muss b2b oder b2c sein.');
        }

        $this->validate($contact->type, $contact->toArray());

        $id = ++$this->sequence;
        $data = $this->toData($id, $contact->type, $contact->toArray(), customerNumber: sprintf('K-%05d', $id));
        $this->contacts[$id] = $data;
        $this->actors[] = ['action' => 'create', 'contact_id' => $id, 'actor' => $actor];

        return $data;
    }

    public function update(int $id, ContactChanges $changes, ActorRef $actor): ContactData
    {
        $current = $this->contacts[$id] ?? throw ContactNotFoundException::forId($id);

        $this->validate($current->type, $changes->toArray(), $current->vatId);

        $data = $this->toData($id, $current->type, $changes->toArray(), customerNumber: $current->customerNumber, direction: $current->direction, previous: $current);
        $this->contacts[$id] = $data;
        $this->actors[] = ['action' => 'update', 'contact_id' => $id, 'actor' => $actor];

        return $data;
    }

    /**
     * @return list<array{action: string, contact_id: int, actor: ActorRef}>
     */
    public function actors(): array
    {
        return $this->actors;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function validate(string $type, array $fields, ?string $previousVatId = null): void
    {
        $errors = [];
        $required = ['email', 'street', 'house_number', 'postal_code', 'city', 'country_code'];
        $required[] = $type === 'b2b' ? 'company_name' : 'last_name';

        foreach ($required as $field) {
            if (trim((string) ($fields[$field] ?? '')) === '') {
                $errors[$field][] = 'Dieses Feld ist erforderlich.';
            }
        }

        if (filter_var((string) ($fields['email'] ?? ''), FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'][] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        }

        $country = strtoupper((string) ($fields['country_code'] ?? ''));

        if (preg_match('/^[A-Z]{2}$/', $country) !== 1) {
            $errors['country_code'][] = 'Das Land muss als ISO-Code mit zwei Buchstaben angegeben werden.';
        }

        $vatId = $fields['vat_id'] ?? null;

        if ($vatId !== null && $vatId !== '') {
            $normalized = self::normalizeVatId((string) $vatId);

            if ($type !== 'b2b') {
                $errors['vat_id'][] = 'USt-ID darf nur für B2B-Kontakte gesetzt werden.';
            } elseif (! str_starts_with($normalized, $country === 'GR' ? 'EL' : $country)) {
                $errors['vat_id'][] = "USt-ID-Land muss mit dem Adressland ({$country}) übereinstimmen.";
            } elseif ($normalized !== self::normalizeVatId((string) $previousVatId) && array_key_exists($normalized, $this->vatIds)) {
                if ($this->vatIds[$normalized] === false) {
                    $errors['vat_id'][] = 'Die USt-ID ist laut VIES ungültig.';
                } elseif ($this->vatIds[$normalized] === null) {
                    $errors['vat_id'][] = 'VIES-Dienst für dieses Land derzeit nicht erreichbar.';
                }
            }
        }

        if ($errors !== []) {
            throw new ContactValidationException($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function toData(int $id, string $type, array $fields, ?string $customerNumber, string $direction = 'customer', ?ContactData $previous = null): ContactData
    {
        $vatId = ($fields['vat_id'] ?? null) === null || $fields['vat_id'] === '' ? null : self::normalizeVatId((string) $fields['vat_id']);
        $isPerson = $type === 'b2c';
        $displayName = $type === 'b2b' && (string) ($fields['company_name'] ?? '') !== ''
            ? (string) $fields['company_name']
            : trim(implode(' ', array_filter([$fields['first_name'] ?? null, $fields['last_name'] ?? null])));

        return new ContactData(
            id: $id,
            displayName: $displayName !== '' ? $displayName : 'Unbenannt',
            type: $type,
            direction: $direction,
            companyName: $fields['company_name'] ?? null,
            firstName: $fields['first_name'] ?? null,
            lastName: $fields['last_name'] ?? null,
            email: $fields['email'] ?? null,
            phone: $fields['phone'] ?? null,
            street: $fields['street'] ?? null,
            houseNumber: $fields['house_number'] ?? null,
            addressAddition: $fields['address_addition'] ?? null,
            postalCode: $fields['postal_code'] ?? null,
            city: $fields['city'] ?? null,
            countryCode: strtoupper((string) ($fields['country_code'] ?? '')),
            vatId: $vatId,
            mobile: $previous?->mobile,
            website: $previous?->website,
            updatedAt: (new \DateTimeImmutable)->format(DATE_ATOM),
            customerNumber: $customerNumber,
            vatIdValid: $vatId === null ? null : ($this->vatIds[$vatId] ?? ($isPerson ? null : true)),
            salutation: $fields['salutation'] ?? null,
        );
    }

    private static function normalizeVatId(string $vatId): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', $vatId));
    }
}
