<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Customer price lists: reads, every write and the price a contact pays.
 *
 * A price list bundles agreed prices and discounts for the contacts
 * assigned to it (one list per contact). The core resolves them at sale
 * time; a module such as a web shop reads the lists or `resolve()`s single
 * prices, and maintains lists and assignments. Every write lands in the
 * core's activity log under `$actor`. Refusals carry the core's German
 * message in a `PriceListException`; unknown ids raise a
 * `PriceListNotFoundException`.
 */
interface PriceLists
{
    public function find(int $id): ?PriceListData;

    /**
     * @return list<PriceListData> ordered by name; inactive lists only with `$includeInactive`
     */
    public function all(bool $includeInactive = false): array;

    /**
     * The list the contact buys on, `null` without an assignment.
     */
    public function forContact(int $contactId): ?PriceListData;

    /**
     * What the contact pays for the price option. Works without a price
     * list (or with an inactive one): the result is then the price-class
     * price with source `CustomerDefault` (customer profile discount) or
     * `None`.
     *
     * @throws PriceListNotFoundException when the contact or the price option is unknown
     */
    public function resolve(int $contactId, int $priceOptionId): CustomerPriceData;

    public function create(NewPriceList $list, ActorRef $actor): PriceListData;

    /**
     * @throws PriceListNotFoundException when the list is unknown
     */
    public function update(int $id, NewPriceList $list, ActorRef $actor): PriceListData;

    /**
     * Deletes the list with its entries; assigned contacts fall back to
     * plain article prices.
     *
     * @return int number of detached contacts
     *
     * @throws PriceListNotFoundException when the list is unknown
     */
    public function delete(int $id, ActorRef $actor): int;

    /**
     * @throws PriceListNotFoundException when the list is unknown
     * @throws PriceListException when the entry is invalid (more than one target, fixed price xor discount, a fixed price needs an article or option, unknown option) or its target already has an entry on the list
     */
    public function addEntry(int $id, NewPriceListEntry $entry, ActorRef $actor): PriceListEntryData;

    /**
     * Replaces target and agreement of the entry.
     *
     * @throws PriceListNotFoundException when the entry is unknown
     * @throws PriceListException on the refusals of `addEntry()`
     */
    public function updateEntry(int $entryId, NewPriceListEntry $entry, ActorRef $actor): PriceListEntryData;

    /**
     * @throws PriceListNotFoundException when the entry is unknown
     */
    public function removeEntry(int $entryId, ActorRef $actor): void;

    /**
     * Puts the contact on the list, `null` takes it off.
     *
     * @throws PriceListNotFoundException when the contact or the list is unknown
     */
    public function assign(int $contactId, ?int $priceListId, ActorRef $actor): void;
}
