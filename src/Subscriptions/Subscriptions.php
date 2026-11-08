<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\ContractException;

/**
 * Read and write access to subscriptions (German UI: "Leistungen") from
 * modules, implemented by the core.
 *
 * A subscription is a service or product a contact receives per period. The
 * core owns everything about money, tax and booking; modules only read
 * subscriptions, create them, bind them to an external source and report
 * source state. `findByLabel()` is the preferred lookup for modules that want
 * to find a subscription by its business-domain name (e.g. a domain name) —
 * active subscriptions come first, then by `id`. `findBySource()` is the
 * preferred lookup for connector modules that want to find a subscription by
 * its external identifier.
 *
 * Service groups (German UI: "Leistungsgruppen") bundle one contact's
 * subscriptions that the billing run bills together on one invoice item group;
 * a subscription is in at most one group. Group writes fire the domain events
 * `SubscriptionGroupCreated`, `SubscriptionGroupUpdated` and
 * `SubscriptionGroupDissolved` after the commit.
 *
 * After the core committed `create()` or `cancel()`, it calls the matching
 * hook of the subscription's `SubscriptionType`.
 */
interface Subscriptions
{
    public function find(int $id): ?SubscriptionData;

    /**
     * @return list<SubscriptionData> Ordered by id.
     */
    public function forContact(int $contactId, ?string $type = null): array;

    /**
     * Active first, then by id.
     *
     * @deprecated since 2.12.0, removed in 3.0: the label is the article name
     *             of the subscription, not the object it stands for; use
     *             {@see findByAttribute()} with the type field that names the
     *             object.
     *
     * @return list<SubscriptionData>
     */
    public function findByLabel(string $type, string $label): array;

    /**
     * Subscriptions of `$type` whose type field `$key` holds exactly `$value`,
     * e.g. the domain subscriptions whose `fqdn` is `example.de`. Active
     * first, then by id.
     *
     * @return list<SubscriptionData>
     */
    public function findByAttribute(string $type, string $key, string $value): array;

    public function findBySource(string $source, string $externalId): ?SubscriptionData;

    /**
     * @return SubscriptionData|null The subscription the invoice item was billed for.
     */
    public function forInvoiceItem(int $invoiceItemId): ?SubscriptionData;

    public function create(NewSubscription $subscription, ActorRef $actor): SubscriptionData;

    /**
     * Sets the source + external identifier on a previously manual
     * subscription, e.g. when a connector subsequently assigns a source to a
     * domain that was created freely.
     *
     * @throws SubscriptionNotFoundException if `$id` is unknown.
     */
    public function attachSource(int $id, string $source, string $externalId): SubscriptionData;

    /**
     * Clears `source` and `externalId` of the subscription again, e.g. when a
     * connector module unlinks an object; a no-op when it has no source.
     *
     * @throws SubscriptionNotFoundException if `$id` is unknown.
     */
    public function detachSource(int $id): SubscriptionData;

    /**
     * Applies a connector's report to the subscription: `renewsAt` becomes
     * `renewsOn` (date part), `autorenew` is taken over, `status` becomes
     * `externalStatus`, and `attributes` are merged with the existing ones,
     * not replaced.
     *
     * @throws SubscriptionNotFoundException if `$id` is unknown.
     */
    public function updateFromSource(int $id, ConnectorStatus $status): SubscriptionData;

    /**
     * Cancels the subscription. With `$endsOn = null` it ends on the earliest
     * date allowed by the minimum term and the notice period.
     *
     * @throws SubscriptionNotFoundException if `$id` is unknown.
     * @throws ContractException if `$endsOn` is before the earliest allowed date or the subscription already ended.
     */
    public function cancel(int $id, ?string $endsOn, ActorRef $actor): SubscriptionData;

    /**
     * @return SubscriptionGroupData|null The service group, `null` when `$id` is unknown.
     */
    public function findGroup(int $id): ?SubscriptionGroupData;

    /**
     * @return list<SubscriptionGroupData> The contact's groups ordered by `position`.
     */
    public function groupsForContact(int $contactId): array;

    /**
     * @return SubscriptionGroupData|null The group the subscription belongs to, `null` when it is ungrouped.
     *
     * @throws SubscriptionNotFoundException if `$subscriptionId` is unknown.
     */
    public function groupOf(int $subscriptionId): ?SubscriptionGroupData;

    /**
     * Creates a group at the end of the contact's groups with the given
     * subscriptions as members in that order. Members of another group move
     * over. Fires `SubscriptionGroupCreated` after the commit, plus
     * `SubscriptionGroupUpdated` for every group that lost a member.
     *
     * @throws SubscriptionNotFoundException if a member id is unknown.
     * @throws ContractException if the name is empty or longer than 120 characters, a subscription is listed twice
     *                           („Eine Leistung darf in einer Gruppe nur einmal vorkommen.“) or belongs to another contact
     *                           („Die Leistung „…“ gehört zu einem anderen Kontakt und kann dieser Gruppe nicht zugeordnet werden.“).
     */
    public function createGroup(NewSubscriptionGroup $group, ActorRef $actor): SubscriptionGroupData;

    /**
     * Changes name, collapse flag and note; a `null` argument keeps the value,
     * `$clearNote = true` removes the note (and wins over `$note`). Fires
     * `SubscriptionGroupUpdated` only when something changed.
     *
     * @throws SubscriptionGroupNotFoundException if `$id` is unknown.
     * @throws ContractException if the name is empty („Der Gruppenname darf nicht leer sein.“) or longer than 120 characters.
     */
    public function updateGroup(int $id, ?string $name, ?bool $collapseOnInvoice, ?string $note, bool $clearNote, ActorRef $actor): SubscriptionGroupData;

    /**
     * Sets the order of the contact's groups. Fires `SubscriptionGroupUpdated`
     * for each group whose position changed.
     *
     * @param  list<int>  $groupIds  Exactly the contact's groups, each once.
     *
     * @throws ContractException if the list does not name exactly the contact's groups
     *                           („Die Reihenfolge muss genau die Gruppen des Kontakts enthalten, jede einmal.“).
     */
    public function reorderGroups(int $contactId, array $groupIds, ActorRef $actor): void;

    /**
     * Replaces the member list in the given order. Subscriptions of another
     * group move over (that group is renumbered), dropped members are
     * ungrouped. Fires `SubscriptionGroupUpdated` for every group whose member
     * list changed.
     *
     * @param  list<int>  $subscriptionIds
     *
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     * @throws SubscriptionNotFoundException if a subscription id is unknown.
     * @throws ContractException on a duplicate or foreign-contact subscription (messages as in `createGroup()`).
     */
    public function setGroupMembers(int $groupId, array $subscriptionIds, ActorRef $actor): SubscriptionGroupData;

    /**
     * Adds a subscription at the 1-based `$position`, at the end when `null`
     * (out-of-range positions are clamped); a member already in the group is
     * moved there, a member of another group moves over.
     *
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     * @throws SubscriptionNotFoundException if `$subscriptionId` is unknown.
     * @throws ContractException if the subscription belongs to another contact.
     */
    public function addGroupMember(int $groupId, int $subscriptionId, ?int $position, ActorRef $actor): SubscriptionGroupData;

    /**
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     * @throws SubscriptionNotFoundException if `$subscriptionId` is unknown.
     * @throws ContractException if the subscription is not a member („Die Leistung „…“ gehört nicht zu dieser Gruppe.“).
     */
    public function removeGroupMember(int $groupId, int $subscriptionId, ActorRef $actor): SubscriptionGroupData;

    /**
     * Ungroups all members and deletes the group; the contact's remaining
     * groups are renumbered. Fires `SubscriptionGroupDissolved`.
     *
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     */
    public function dissolveGroup(int $groupId, ActorRef $actor): void;

    /**
     * Cancels all active members of the group to the shared last day
     * `$endsOn` (`YYYY-MM-DD`). The date is checked against every member
     * first; if one rejects it nothing changes. Already cancelled or ended
     * members are skipped. There is no "earliest possible" date for a group.
     *
     * @return list<SubscriptionData> The members cancelled by this call, in group order.
     *
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     * @throws ContractException if `$endsOn` is `null` („Für eine Gruppe muss ein gemeinsames Enddatum gewählt werden.“),
     *                           malformed, or rejected by a member.
     */
    public function cancelGroup(int $groupId, ?string $endsOn, ActorRef $actor): array;

    /**
     * The last days all active members can be cancelled to (`YYYY-MM-DD`,
     * ascending): the intersection of the end options of the members. Members
     * without end options accept any date and do not narrow the list. The
     * core returns at most 12 dates.
     *
     * `null` means no active member has a billing grid, so any date is valid.
     * An empty list means constrained members share no period boundary: the
     * group cannot be cancelled as a whole.
     *
     * @return list<string>|null
     *
     * @throws SubscriptionGroupNotFoundException if `$groupId` is unknown.
     */
    public function groupEndOptions(int $groupId): ?array;
}
