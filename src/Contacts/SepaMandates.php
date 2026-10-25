<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

use MyTechIO\Contracts\ContractException;

/**
 * SEPA direct debit mandates of core contacts.
 *
 * A module that collects payments (e.g. a payment provider) reads the
 * mandates of a contact and registers or revokes mandates it manages
 * itself. The core owns the mandate records; a module identifies its own
 * mandates by `source` and `externalId`.
 */
interface SepaMandates
{
    public function find(int $id): ?SepaMandateData;

    /**
     * @return list<SepaMandateData> newest first
     */
    public function forContact(int $contactId): array;

    /**
     * The newest active mandate of the contact, `null` without one.
     */
    public function active(int $contactId): ?SepaMandateData;

    /**
     * @throws ContractException when the contact is missing, the reference is taken or (source, externalId) already exists
     */
    public function create(NewSepaMandate $mandate): SepaMandateData;

    /**
     * Idempotent: an already revoked mandate stays revoked.
     *
     * @throws ContractException when unknown
     */
    public function revoke(int $id, string $reason): SepaMandateData;
}
