<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Contacts\ContactData;
use MyTechIO\Contracts\Contacts\Contacts;

/**
 * Test double for `Contacts`: in-memory store, `seed()` sets the
 * initial state. `search()` filters simply by substring
 * (case-insensitive) over `displayName` — sufficient for module tests,
 * no relevance ranking like in the core.
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

    public function seed(ContactData $contact): void
    {
        $this->contacts[$contact->id] = $contact;
    }

    public function find(int $id): ?ContactData
    {
        return $this->contacts[$id] ?? null;
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
}
