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
     * Contacts by id in one lookup, keyed by id; unknown ids are left out. The order of the result is unspecified.
     *
     * @param  list<int>  $ids
     * @return array<int, ContactData>
     */
    public function findMany(array $ids): array;

    /**
     * @return list<ContactData>
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * All active contacts on the supplier side (`direction` `supplier` or
     * `both`), ordered by display name — for client-side pickers that
     * filter the whole list themselves.
     *
     * @return list<ContactData>
     */
    public function suppliers(): array;

    /**
     * @return array<string, string> ISO-2 ⇒ country name (German)
     */
    public function countries(): array;
}
