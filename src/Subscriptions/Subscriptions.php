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
}
