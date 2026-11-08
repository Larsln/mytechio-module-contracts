<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;
use MyTechIO\Contracts\Subscriptions\NewSubscription;
use MyTechIO\Contracts\Subscriptions\NewSubscriptionGroup;
use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionGroupData;
use MyTechIO\Contracts\Subscriptions\SubscriptionGroupNotFoundException;
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
 *
 * Service groups are held in memory with the core's rules: members of one
 * contact only, a subscription in at most one group (it moves over), dense
 * positions of groups per contact and of members per group. `seedGroup()`
 * seeds a group (its members' `groupId`/`groupPosition` are then filled on
 * every read); `seedEndOptions()` gives a subscription end options, which
 * `groupEndOptions()` intersects (`null` while no active member has end options,
 * `[]` for an empty intersection) and `cancelGroup()` enforces (a subscription
 * without end options accepts any date). Group writes record
 * `SubscriptionGroupCreated`, `SubscriptionGroupUpdated` and
 * `SubscriptionGroupDissolved` with payload and actor in `groupEvents()`.
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

    /**
     * @var array<int, array{id: int, contactId: int, name: string, collapseOnInvoice: bool, note: ?string, createdAt: string, memberIds: list<int>}>
     */
    private array $groups = [];

    /**
     * @var array<int, list<string>>
     */
    private array $endOptions = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>, actor: ActorRef}>
     */
    private array $groupEvents = [];

    private int $nextId = 1;

    private int $nextGroupId = 1;

    /**
     * @var array<int, int>
     */
    private array $groupPositions = [];

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
        $subscription = $this->subscriptions[$id] ?? null;

        return $subscription === null ? null : $this->hydrate($subscription);
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
        return $this->activeFirst(
            static fn (SubscriptionData $subscription): bool => $subscription->type === $type && $subscription->label === $label,
        );
    }

    public function findByAttribute(string $type, string $key, string $value): array
    {
        return $this->activeFirst(
            static fn (SubscriptionData $subscription): bool => $subscription->type === $type && ($subscription->attributes[$key] ?? null) === $value,
        );
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
            paymentMethod: $subscription->paymentMethod,
        );

        $this->subscriptions[$id] = $data;
        $this->actors[] = ['action' => 'create', 'id' => $id, 'actor' => $actor];

        return $this->hydrate($data);
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

    public function seedGroup(SubscriptionGroupData $group): void
    {
        $this->groups[$group->id] = [
            'id' => $group->id,
            'contactId' => $group->contactId,
            'name' => $group->name,
            'collapseOnInvoice' => $group->collapseOnInvoice,
            'note' => $group->note,
            'createdAt' => $group->createdAt,
            'memberIds' => $group->memberIds,
        ];
        $this->nextGroupId = max($this->nextGroupId, $group->id + 1);
        $this->groupPositions[$group->id] = $group->position;
    }

    /**
     * The last days the subscription can be cancelled to (`YYYY-MM-DD`).
     *
     * @param  list<string>  $dates
     *
     * @throws SubscriptionNotFoundException if `$subscriptionId` is unknown.
     */
    public function seedEndOptions(int $subscriptionId, array $dates): void
    {
        $this->get($subscriptionId);

        foreach ($dates as $date) {
            Validate::date($date, 'end option');
        }

        $dates = array_values(array_unique($dates));
        sort($dates);
        $this->endOptions[$subscriptionId] = $dates;
    }

    public function findGroup(int $id): ?SubscriptionGroupData
    {
        return isset($this->groups[$id]) ? $this->groupData($id) : null;
    }

    public function groupsForContact(int $contactId): array
    {
        return array_map(
            fn (int $id): SubscriptionGroupData => $this->groupData($id),
            $this->groupIdsOf($contactId),
        );
    }

    public function groupOf(int $subscriptionId): ?SubscriptionGroupData
    {
        $this->get($subscriptionId);
        $groupId = $this->groupIdOfMember($subscriptionId);

        return $groupId === null ? null : $this->groupData($groupId);
    }

    public function createGroup(NewSubscriptionGroup $group, ActorRef $actor): SubscriptionGroupData
    {
        $name = $this->validName($group->name);
        $this->assertMembers($group->contactId, $group->memberIds);

        $id = $this->nextGroupId++;
        $this->groupPositions[$id] = count($this->groupIdsOf($group->contactId)) + 1;
        $this->groups[$id] = [
            'id' => $id,
            'contactId' => $group->contactId,
            'name' => $name,
            'collapseOnInvoice' => $group->collapseOnInvoice,
            'note' => $group->note,
            'createdAt' => gmdate('c'),
            'memberIds' => [],
        ];

        $this->syncMembers($id, $group->memberIds, $actor, announceGroup: false);
        $this->recordGroupEvent('SubscriptionGroupCreated', $id, $actor, ['member_ids' => $this->groups[$id]['memberIds']]);

        return $this->groupData($id);
    }

    public function updateGroup(int $id, ?string $name, ?bool $collapseOnInvoice, ?string $note, bool $clearNote, ActorRef $actor): SubscriptionGroupData
    {
        $group = $this->group($id);
        $changed = $group;

        if ($name !== null) {
            $changed['name'] = $this->validName($name);
        }

        if ($collapseOnInvoice !== null) {
            $changed['collapseOnInvoice'] = $collapseOnInvoice;
        }

        if ($clearNote) {
            $changed['note'] = null;
        } elseif ($note !== null) {
            $changed['note'] = $note;
        }

        if ($changed !== $group) {
            $this->groups[$id] = $changed;
            $this->recordUpdated($id, $actor);
        }

        return $this->groupData($id);
    }

    public function reorderGroups(int $contactId, array $groupIds, ActorRef $actor): void
    {
        $current = $this->groupIdsOf($contactId);
        $given = array_values($groupIds);

        $expected = $current;
        sort($expected);
        $sorted = $given;
        sort($sorted);

        if ($expected !== $sorted) {
            throw new ContractException('Die Reihenfolge muss genau die Gruppen des Kontakts enthalten, jede einmal.');
        }

        foreach ($given as $index => $groupId) {
            if ($this->groupPositions[$groupId] !== $index + 1) {
                $this->groupPositions[$groupId] = $index + 1;
                $this->recordUpdated($groupId, $actor);
            }
        }
    }

    public function setGroupMembers(int $groupId, array $subscriptionIds, ActorRef $actor): SubscriptionGroupData
    {
        $group = $this->group($groupId);
        $subscriptionIds = array_values($subscriptionIds);
        $this->assertMembers($group['contactId'], $subscriptionIds);
        $this->syncMembers($groupId, $subscriptionIds, $actor);

        return $this->groupData($groupId);
    }

    public function addGroupMember(int $groupId, int $subscriptionId, ?int $position, ActorRef $actor): SubscriptionGroupData
    {
        $group = $this->group($groupId);
        $this->assertMembers($group['contactId'], [$subscriptionId]);

        $ids = array_values(array_filter($group['memberIds'], static fn (int $id): bool => $id !== $subscriptionId));
        array_splice($ids, $position === null ? count($ids) : max(0, min($position - 1, count($ids))), 0, [$subscriptionId]);
        $this->syncMembers($groupId, $ids, $actor);

        return $this->groupData($groupId);
    }

    public function removeGroupMember(int $groupId, int $subscriptionId, ActorRef $actor): SubscriptionGroupData
    {
        $group = $this->group($groupId);
        $subscription = $this->get($subscriptionId);

        if (! in_array($subscriptionId, $group['memberIds'], true)) {
            throw new ContractException(sprintf('Die Leistung „%s“ gehört nicht zu dieser Gruppe.', $subscription->label));
        }

        $this->syncMembers($groupId, array_values(array_diff($group['memberIds'], [$subscriptionId])), $actor);

        return $this->groupData($groupId);
    }

    public function dissolveGroup(int $groupId, ActorRef $actor): void
    {
        $group = $this->group($groupId);
        $payload = ['former_member_ids' => $group['memberIds']];

        $this->recordGroupEvent('SubscriptionGroupDissolved', $groupId, $actor, $payload);

        unset($this->groups[$groupId], $this->groupPositions[$groupId]);
        $this->densifyGroupPositions($group['contactId']);
    }

    public function cancelGroup(int $groupId, ?string $endsOn, ActorRef $actor): array
    {
        $group = $this->group($groupId);

        if ($endsOn === null) {
            throw new ContractException('Für eine Gruppe muss ein gemeinsames Enddatum gewählt werden.');
        }

        Validate::date($endsOn, 'endsOn');

        $members = array_values(array_filter(
            array_map($this->get(...), $group['memberIds']),
            static fn (SubscriptionData $member): bool => $member->status === SubscriptionStatus::Active,
        ));

        foreach ($members as $member) {
            $options = $this->endOptions[$member->id] ?? [];

            if ($options !== [] && ! in_array($endsOn, $options, true)) {
                throw new ContractException(sprintf(
                    'Die Gruppe kann nicht zum %s gekündigt werden: Leistung „%s“ – %s ist kein möglicher Endtermin.',
                    date('d.m.Y', strtotime($endsOn)),
                    $member->label,
                    date('d.m.Y', strtotime($endsOn)),
                ));
            }
        }

        return array_map(fn (SubscriptionData $member): SubscriptionData => $this->cancel($member->id, $endsOn, $actor), $members);
    }

    public function groupEndOptions(int $groupId): ?array
    {
        $common = null;

        foreach ($this->group($groupId)['memberIds'] as $memberId) {
            $options = $this->endOptions[$memberId] ?? [];

            if ($this->get($memberId)->status !== SubscriptionStatus::Active || $options === []) {
                continue;
            }

            $common = $common === null ? $options : array_values(array_intersect($common, $options));
        }

        return $common === null ? null : array_slice($common, 0, 12);
    }

    /**
     * Recorded group events in order: `SubscriptionGroupCreated`,
     * `SubscriptionGroupUpdated` and `SubscriptionGroupDissolved`, with the
     * snake_case payload of the core's domain event and the acting `ActorRef`.
     *
     * @return list<array{event: string, payload: array<string, mixed>, actor: ActorRef}>
     */
    public function groupEvents(): array
    {
        return $this->groupEvents;
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

        return array_map($this->hydrate(...), array_values($subscriptions));
    }

    /**
     * The subscriptions `$matches` accepts, active ones first and then by id.
     *
     * @param  \Closure(SubscriptionData): bool  $matches
     * @return list<SubscriptionData>
     */
    private function activeFirst(\Closure $matches): array
    {
        $matches = array_values(array_filter($this->sorted(), $matches));

        usort($matches, static function (SubscriptionData $a, SubscriptionData $b): int {
            $activeA = $a->status === SubscriptionStatus::Active ? 0 : 1;
            $activeB = $b->status === SubscriptionStatus::Active ? 0 : 1;

            return $activeA <=> $activeB ?: $a->id <=> $b->id;
        });

        return $matches;
    }

    private function get(int $id): SubscriptionData
    {
        return $this->find($id) ?? throw new SubscriptionNotFoundException($id);
    }

    /**
     * @param  array<string, mixed>  $changes  Constructor arguments of `SubscriptionData` to override.
     */
    private function replace(SubscriptionData $current, array $changes): SubscriptionData
    {
        $updated = new SubscriptionData(...[...get_object_vars($current), ...$changes]);

        $this->subscriptions[$updated->id] = $updated;

        return $this->hydrate($updated);
    }

    private function hydrate(SubscriptionData $subscription): SubscriptionData
    {
        $groupId = $this->groupIdOfMember($subscription->id);

        if ($groupId === null) {
            return $subscription->groupId === null ? $subscription : $this->withGroup($subscription, null, null);
        }

        return $this->withGroup($subscription, $groupId, array_search($subscription->id, $this->groups[$groupId]['memberIds'], true) + 1);
    }

    private function withGroup(SubscriptionData $subscription, ?int $groupId, ?int $position): SubscriptionData
    {
        return new SubscriptionData(...[...get_object_vars($subscription), 'groupId' => $groupId, 'groupPosition' => $position]);
    }

    private function groupIdOfMember(int $subscriptionId): ?int
    {
        foreach ($this->groups as $group) {
            if (in_array($subscriptionId, $group['memberIds'], true)) {
                return $group['id'];
            }
        }

        return null;
    }

    /**
     * @return array{id: int, contactId: int, name: string, collapseOnInvoice: bool, note: ?string, createdAt: string, memberIds: list<int>}
     */
    private function group(int $id): array
    {
        return $this->groups[$id] ?? throw new SubscriptionGroupNotFoundException($id);
    }

    private function groupData(int $id): SubscriptionGroupData
    {
        $group = $this->group($id);

        return new SubscriptionGroupData(
            id: $id,
            contactId: $group['contactId'],
            name: $group['name'],
            position: $this->groupPositions[$id],
            collapseOnInvoice: $group['collapseOnInvoice'],
            note: $group['note'],
            memberIds: $group['memberIds'],
            invoiceGroupId: SubscriptionGroupData::invoiceGroupIdFor($id),
            createdAt: $group['createdAt'],
        );
    }

    /**
     * @return list<int> The contact's group ids ordered by position.
     */
    private function groupIdsOf(int $contactId): array
    {
        $ids = array_keys(array_filter($this->groups, static fn (array $group): bool => $group['contactId'] === $contactId));
        usort($ids, fn (int $a, int $b): int => $this->groupPositions[$a] <=> $this->groupPositions[$b] ?: $a <=> $b);

        return $ids;
    }

    private function densifyGroupPositions(int $contactId): void
    {
        foreach ($this->groupIdsOf($contactId) as $index => $id) {
            $this->groupPositions[$id] = $index + 1;
        }
    }

    private function validName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            throw new ContractException('Der Gruppenname darf nicht leer sein.');
        }

        if (mb_strlen($name) > 120) {
            throw new ContractException('Der Gruppenname darf höchstens 120 Zeichen lang sein.');
        }

        return $name;
    }

    /**
     * @param  list<int>  $subscriptionIds
     *
     * @throws SubscriptionNotFoundException
     * @throws ContractException
     */
    private function assertMembers(int $contactId, array $subscriptionIds): void
    {
        if (count($subscriptionIds) !== count(array_unique($subscriptionIds))) {
            throw new ContractException('Eine Leistung darf in einer Gruppe nur einmal vorkommen.');
        }

        foreach ($subscriptionIds as $subscriptionId) {
            $subscription = $this->get($subscriptionId);

            if ($subscription->contactId !== $contactId) {
                throw new ContractException(sprintf('Die Leistung „%s“ gehört zu einem anderen Kontakt und kann dieser Gruppe nicht zugeordnet werden.', $subscription->label));
            }
        }
    }

    /**
     * Makes `$subscriptionIds` the validated members of the group, in order,
     * moving members over from other groups, and records an update for every
     * group whose member list changed (`$announceGroup = false` leaves the
     * event of `$groupId` itself to the caller).
     *
     * @param  list<int>  $subscriptionIds
     */
    private function syncMembers(int $groupId, array $subscriptionIds, ActorRef $actor, bool $announceGroup = true): void
    {
        $changed = [];

        foreach ($this->groups as $otherId => $other) {
            if ($otherId !== $groupId && array_intersect($other['memberIds'], $subscriptionIds) !== []) {
                $this->groups[$otherId]['memberIds'] = array_values(array_diff($other['memberIds'], $subscriptionIds));
                $changed[] = $otherId;
            }
        }

        if ($this->groups[$groupId]['memberIds'] !== $subscriptionIds) {
            $this->groups[$groupId]['memberIds'] = $subscriptionIds;

            if ($announceGroup) {
                $changed[] = $groupId;
            }
        }

        foreach ($changed as $changedId) {
            $this->recordUpdated($changedId, $actor);
        }
    }

    private function recordUpdated(int $groupId, ActorRef $actor): void
    {
        $this->recordGroupEvent('SubscriptionGroupUpdated', $groupId, $actor, [
            'collapse_on_invoice' => $this->groups[$groupId]['collapseOnInvoice'],
            'member_ids' => $this->groups[$groupId]['memberIds'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function recordGroupEvent(string $event, int $groupId, ActorRef $actor, array $payload): void
    {
        $group = $this->group($groupId);

        $this->groupEvents[] = [
            'event' => $event,
            'payload' => ['group_id' => $groupId, 'contact_id' => $group['contactId'], ...($event === 'SubscriptionGroupDissolved' ? [] : ['name' => $group['name']]), ...$payload],
            'actor' => $actor,
        ];
    }
}
