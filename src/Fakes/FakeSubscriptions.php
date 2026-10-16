<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\NewSubscription;
use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionNotFoundException;
use MyTechIO\Contracts\Subscriptions\Subscriptions;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;

/**
 * Test double for `Subscriptions`: in-memory store with auto IDs. `seed()` lets
 * tests specify the initial state, including already occupied IDs;
 * `linkInvoiceItem()` connects a subscription to an invoice item for
 * `forInvoiceItem()`. `findByLabel()` sorts active subscriptions first, just
 * like the core implementation. `cancel()` does not model minimum terms or
 * notice periods: without an explicit date the subscription ends on `renewsOn`,
 * or today. `create()` and `cancel()` record their actor in `actors()`.
 */
final class FakeSubscriptions implements Subscriptions
{
    /**
     * @var array<int, SubscriptionData>
     */
    private array $subscriptions = [];

    /**
     * @var array<int, int>
     */
    private array $invoiceItems = [];

    /**
     * @var list<array{action: string, id: int, actor: ActorRef}>
     */
    private array $actors = [];

    private int $nextId = 1;

    public function seed(SubscriptionData $subscription): void
    {
        $this->subscriptions[$subscription->id] = $subscription;
        $this->nextId = max($this->nextId, $subscription->id + 1);
    }

    /**
     * @throws SubscriptionNotFoundException if `$subscriptionId` is unknown.
     */
    public function linkInvoiceItem(int $invoiceItemId, int $subscriptionId): void
    {
        $this->get($subscriptionId);
        $this->invoiceItems[$invoiceItemId] = $subscriptionId;
    }

    public function find(int $id): ?SubscriptionData
    {
        return $this->subscriptions[$id] ?? null;
    }

    public function forContact(int $contactId, ?string $type = null): array
    {
        return array_values(array_filter(
            $this->sorted(),
            static fn (SubscriptionData $subscription): bool => $subscription->contactId === $contactId
                && ($type === null || $subscription->type === $type),
        ));
    }

    public function findByLabel(string $type, string $label): array
    {
        $matches = array_values(array_filter(
            $this->sorted(),
            static fn (SubscriptionData $subscription): bool => $subscription->type === $type && $subscription->label === $label,
        ));

        usort($matches, static function (SubscriptionData $a, SubscriptionData $b): int {
            $activeA = $a->status === SubscriptionStatus::Active ? 0 : 1;
            $activeB = $b->status === SubscriptionStatus::Active ? 0 : 1;

            return $activeA <=> $activeB ?: $a->id <=> $b->id;
        });

        return $matches;
    }

    public function findBySource(string $source, string $externalId): ?SubscriptionData
    {
        foreach ($this->sorted() as $subscription) {
            if ($subscription->source === $source && $subscription->externalId === $externalId) {
                return $subscription;
            }
        }

        return null;
    }

    public function forInvoiceItem(int $invoiceItemId): ?SubscriptionData
    {
        $subscriptionId = $this->invoiceItems[$invoiceItemId] ?? null;

        return $subscriptionId === null ? null : $this->find($subscriptionId);
    }

    public function create(NewSubscription $subscription, ActorRef $actor): SubscriptionData
    {
        $id = $this->nextId++;

        $data = new SubscriptionData(
            id: $id,
            contactId: $subscription->contactId,
            type: $subscription->type,
            label: $subscription->label,
            status: SubscriptionStatus::Active,
            articleId: $subscription->articleId,
            priceOptionId: $subscription->priceOptionId,
            quantity: $subscription->quantity,
            startsOn: $subscription->startsOn ?? date('Y-m-d'),
            source: $subscription->source,
            externalId: $subscription->externalId,
            providerName: $subscription->providerName,
            renewsOn: $subscription->renewsOn,
            autorenew: $subscription->autorenew,
            attributes: $subscription->attributes,
        );

        $this->subscriptions[$id] = $data;
        $this->actors[] = ['action' => 'create', 'id' => $id, 'actor' => $actor];

        return $data;
    }

    public function attachSource(int $id, string $source, string $externalId): SubscriptionData
    {
        return $this->replace($this->get($id), ['source' => $source, 'externalId' => $externalId]);
    }

    public function detachSource(int $id): SubscriptionData
    {
        return $this->replace($this->get($id), ['source' => null, 'externalId' => null]);
    }

    public function updateFromSource(int $id, ConnectorStatus $status): SubscriptionData
    {
        $current = $this->get($id);

        return $this->replace($current, [
            'renewsOn' => $status->renewsAt === null ? null : substr($status->renewsAt, 0, 10),
            'autorenew' => $status->autorenew,
            'externalStatus' => $status->status,
            'attributes' => [...$current->attributes, ...$status->attributes],
        ]);
    }

    public function cancel(int $id, ?string $endsOn, ActorRef $actor): SubscriptionData
    {
        $current = $this->get($id);

        if ($current->status === SubscriptionStatus::Ended) {
            throw new ContractException(sprintf('Leistung mit ID %d ist bereits beendet.', $id));
        }

        $today = date('Y-m-d');

        $cancelled = $this->replace($current, [
            'status' => SubscriptionStatus::Cancelled,
            'cancelledOn' => $today,
            'endsOn' => $endsOn ?? $current->renewsOn ?? $today,
        ]);

        $this->actors[] = ['action' => 'cancel', 'id' => $id, 'actor' => $actor];

        return $cancelled;
    }

    /**
     * Recorded actors of `create()` and `cancel()` calls in order.
     *
     * @return list<array{action: string, id: int, actor: ActorRef}>
     */
    public function actors(): array
    {
        return $this->actors;
    }

    /**
     * @return list<SubscriptionData>
     */
    private function sorted(): array
    {
        $subscriptions = $this->subscriptions;
        ksort($subscriptions);

        return array_values($subscriptions);
    }

    private function get(int $id): SubscriptionData
    {
        return $this->subscriptions[$id] ?? throw new SubscriptionNotFoundException($id);
    }

    /**
     * @param  array<string, mixed>  $changes  Constructor arguments of `SubscriptionData` to override.
     */
    private function replace(SubscriptionData $current, array $changes): SubscriptionData
    {
        $updated = new SubscriptionData(...[...get_object_vars($current), ...$changes]);

        $this->subscriptions[$updated->id] = $updated;

        return $updated;
    }
}
