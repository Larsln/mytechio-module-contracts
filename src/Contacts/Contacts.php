<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Access to core contacts: reads for every module, and since 2.15 the
 * creation and update of customer contacts for modules that onboard
 * customers themselves (e.g. a web shop). Writes run through the core's
 * contact service, so the activity log and `ContactCreated`/`ContactUpdated`
 * fire like for the UI; a new or changed VAT ID is checked against VIES by
 * the core. Deleting stays with the core.
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

    /**
     * Contacts whose primary email matches exactly (case-insensitive), deleted
     * ones left out, in creation order. Since 2.15.
     *
     * @return list<ContactData>
     */
    public function findByEmail(string $email): array;

    /**
     * Creates a customer contact (direction `customer`) with the customer-facing
     * data. Since 2.15.
     *
     * @throws ContactValidationException when the core refuses the data (missing fields, VAT ID invalid or not checkable)
     */
    public function create(NewContact $contact, ActorRef $actor): ContactData;

    /**
     * Replaces the customer-facing fields of a contact and keeps everything
     * else. Since 2.15.
     *
     * @throws ContactNotFoundException
     * @throws ContactValidationException
     */
    public function update(int $id, ContactChanges $changes, ActorRef $actor): ContactData;
}
