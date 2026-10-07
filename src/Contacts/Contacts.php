<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * Read-only access to core contacts.
 *
 * Modules may look up and search contacts via this contract, but not
 * create or modify them — contact master data remains exclusively the
 * core's responsibility.
 */
interface Contacts
{
    public function find(int $id): ?ContactData;

    /**
     * @return list<ContactData>
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * @return array<string, string> ISO-2 ⇒ country name (German)
     */
    public function countries(): array;
}
