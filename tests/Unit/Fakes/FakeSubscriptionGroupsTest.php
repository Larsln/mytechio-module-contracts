<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeSubscriptions;
use MyTechIO\Contracts\Subscriptions\NewSubscriptionGroup;
use MyTechIO\Contracts\Subscriptions\SubscriptionGroupData;
use MyTechIO\Contracts\Subscriptions\SubscriptionGroupNotFoundException;
use MyTechIO\Contracts\Subscriptions\SubscriptionNotFoundException;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;

function groupActor(): ActorRef
{
    return new ActorRef(7, 'Lars');
}

/**
 * Subscriptions 1-3 of contact 1, 4 of contact 2.
 */
function fakeWithSubscriptions(): FakeSubscriptions
{
    $fake = new FakeSubscriptions;

    foreach ([1 => 1, 2 => 1, 3 => 1, 4 => 2] as $id => $contactId) {
        $fake->seed(seededSubscription($id, ['contactId' => $contactId, 'label' => "Leistung {$id}"]));
    }

    return $fake;
}

/**
 * @return list<string>
 */
function groupEventNames(FakeSubscriptions $fake): array
{
    return array_map(static fn (array $event): string => $event['event'].':'.$event['payload']['group_id'], $fake->groupEvents());
}

it('computes the invoice group id as a deterministic UUIDv5', function () {
    expect(SubscriptionGroupData::invoiceGroupIdFor(1))->toBe(SubscriptionGroupData::invoiceGroupIdFor(1))
        ->and(SubscriptionGroupData::invoiceGroupIdFor(1))->not->toBe(SubscriptionGroupData::invoiceGroupIdFor(2))
        ->and(SubscriptionGroupData::invoiceGroupIdFor(1))->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/')
        ->and(SubscriptionGroupData::invoiceGroupIdFor(1))->toBe(uuid5Reference('mytechio:subscription-group:1'));
});

/**
 * Independent UUIDv5 (URL namespace) implementation to cross-check the contract helper.
 */
function uuid5Reference(string $name): string
{
    $bytes = substr(sha1(hex2bin('6ba7b8119dad11d180b400c04fd430c8').$name, true), 0, 16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x50);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

it('creates a group with ordered members and fills the members group fields', function () {
    $fake = fakeWithSubscriptions();

    $group = $fake->createGroup(new NewSubscriptionGroup(1, '  Hosting  ', false, 'Notiz', [3, 1]), groupActor());

    expect($group->name)->toBe('Hosting')
        ->and($group->position)->toBe(1)
        ->and($group->collapseOnInvoice)->toBeFalse()
        ->and($group->note)->toBe('Notiz')
        ->and($group->memberIds)->toBe([3, 1])
        ->and($group->invoiceGroupId)->toBe(SubscriptionGroupData::invoiceGroupIdFor($group->id))
        ->and($fake->find(3)?->groupId)->toBe($group->id)
        ->and($fake->find(3)?->groupPosition)->toBe(1)
        ->and($fake->find(1)?->groupPosition)->toBe(2)
        ->and($fake->find(2)?->groupId)->toBeNull()
        ->and($fake->forContact(1)[2]->groupPosition)->toBe(1)
        ->and($fake->findGroup($group->id))->toEqual($group)
        ->and($fake->findGroup(99))->toBeNull()
        ->and($fake->groupOf(1)?->id)->toBe($group->id)
        ->and($fake->groupOf(2))->toBeNull();
});

it('appends groups with dense positions per contact and lists them in order', function () {
    $fake = fakeWithSubscriptions();

    $first = $fake->createGroup(new NewSubscriptionGroup(1, 'A'), groupActor());
    $second = $fake->createGroup(new NewSubscriptionGroup(1, 'B'), groupActor());
    $other = $fake->createGroup(new NewSubscriptionGroup(2, 'C'), groupActor());

    expect([$first->position, $second->position, $other->position])->toBe([1, 2, 1])
        ->and(array_map(fn ($g) => $g->id, $fake->groupsForContact(1)))->toBe([$first->id, $second->id])
        ->and($fake->groupsForContact(3))->toBe([]);
});

it('refuses invalid group input on create', function (NewSubscriptionGroup $group, string $message, string $exception = ContractException::class) {
    $fake = fakeWithSubscriptions();

    expect(fn () => $fake->createGroup($group, groupActor()))->toThrow($exception, $message)
        ->and($fake->groupsForContact(1))->toBe([])
        ->and($fake->groupEvents())->toBe([]);
})->with([
    'empty name' => [fn () => new NewSubscriptionGroup(1, '   '), 'Der Gruppenname darf nicht leer sein.'],
    'long name' => [fn () => new NewSubscriptionGroup(1, str_repeat('x', 121)), 'höchstens 120 Zeichen'],
    'duplicate member' => [fn () => new NewSubscriptionGroup(1, 'A', memberIds: [1, 1]), 'nur einmal vorkommen'],
    'foreign contact' => [fn () => new NewSubscriptionGroup(1, 'A', memberIds: [1, 4]), 'gehört zu einem anderen Kontakt'],
    'unknown member' => [fn () => new NewSubscriptionGroup(1, 'A', memberIds: [99]), 'Leistung mit ID 99', SubscriptionNotFoundException::class],
]);

it('updates name, collapse flag and note and keeps null arguments', function () {
    $fake = fakeWithSubscriptions();
    $group = $fake->createGroup(new NewSubscriptionGroup(1, 'A', true, 'alt', [1]), groupActor());

    $renamed = $fake->updateGroup($group->id, 'B', false, null, false, groupActor());
    expect($renamed->name)->toBe('B')->and($renamed->collapseOnInvoice)->toBeFalse()->and($renamed->note)->toBe('alt');

    $noted = $fake->updateGroup($group->id, null, null, 'neu', false, groupActor());
    expect($noted->note)->toBe('neu')->and($noted->name)->toBe('B');

    $cleared = $fake->updateGroup($group->id, null, null, 'ignoriert', true, groupActor());
    expect($cleared->note)->toBeNull();
});

it('fires an update event only when the group changed', function () {
    $fake = fakeWithSubscriptions();
    $group = $fake->createGroup(new NewSubscriptionGroup(1, 'A', true, null, [1]), groupActor());
    $count = count($fake->groupEvents());

    $fake->updateGroup($group->id, 'A', true, null, false, groupActor());
    expect($fake->groupEvents())->toHaveCount($count);

    $fake->updateGroup($group->id, 'B', null, null, false, groupActor());
    $event = $fake->groupEvents()[$count];

    expect($event['event'])->toBe('SubscriptionGroupUpdated')
        ->and($event['payload'])->toBe(['group_id' => $group->id, 'contact_id' => 1, 'name' => 'B', 'collapse_on_invoice' => true, 'member_ids' => [1]])
        ->and($event['actor'])->toEqual(groupActor());
});

it('refuses invalid update input and unknown groups', function () {
    $fake = fakeWithSubscriptions();
    $group = $fake->createGroup(new NewSubscriptionGroup(1, 'A'), groupActor());

    expect(fn () => $fake->updateGroup($group->id, ' ', null, null, false, groupActor()))->toThrow(ContractException::class, 'nicht leer')
        ->and(fn () => $fake->updateGroup(99, 'X', null, null, false, groupActor()))->toThrow(SubscriptionGroupNotFoundException::class, 'ID 99');
});

it('reorders the groups of a contact and fires updates for moved groups only', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A'), groupActor());
    $b = $fake->createGroup(new NewSubscriptionGroup(1, 'B'), groupActor());
    $c = $fake->createGroup(new NewSubscriptionGroup(1, 'C'), groupActor());
    $before = count($fake->groupEvents());

    $fake->reorderGroups(1, [$a->id, $c->id, $b->id], groupActor());

    expect(array_map(fn ($g) => $g->name, $fake->groupsForContact(1)))->toBe(['A', 'C', 'B'])
        ->and(array_map(fn ($g) => $g->position, $fake->groupsForContact(1)))->toBe([1, 2, 3])
        ->and(array_slice(groupEventNames($fake), $before))->toBe(["SubscriptionGroupUpdated:{$c->id}", "SubscriptionGroupUpdated:{$b->id}"]);
});

it('refuses a reorder that does not name exactly the contact groups', function (array $ids) {
    $fake = fakeWithSubscriptions();
    $fake->createGroup(new NewSubscriptionGroup(1, 'A'), groupActor());
    $fake->createGroup(new NewSubscriptionGroup(1, 'B'), groupActor());
    $fake->createGroup(new NewSubscriptionGroup(2, 'C'), groupActor());

    $fake->reorderGroups(1, $ids, groupActor());
})->with([
    'missing' => [[1]],
    'duplicate' => [[1, 1]],
    'foreign group' => [[1, 3]],
])->throws(ContractException::class, 'Die Reihenfolge muss genau die Gruppen des Kontakts enthalten, jede einmal.');

it('replaces the members, moves members over from other groups and renumbers', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2]), groupActor());
    $b = $fake->createGroup(new NewSubscriptionGroup(1, 'B', memberIds: [3]), groupActor());
    $before = count($fake->groupEvents());

    $result = $fake->setGroupMembers($b->id, [2, 3], groupActor());

    expect($result->memberIds)->toBe([2, 3])
        ->and($fake->findGroup($a->id)?->memberIds)->toBe([1])
        ->and($fake->find(1)?->groupPosition)->toBe(1)
        ->and($fake->find(2)?->groupId)->toBe($b->id)
        ->and(array_slice(groupEventNames($fake), $before))->toBe(["SubscriptionGroupUpdated:{$a->id}", "SubscriptionGroupUpdated:{$b->id}"]);

    $fake->setGroupMembers($b->id, [], groupActor());
    expect($fake->find(2)?->groupId)->toBeNull()->and($fake->findGroup($b->id)?->memberIds)->toBe([]);
});

it('fires no event when the member list is unchanged', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2]), groupActor());
    $count = count($fake->groupEvents());

    $fake->setGroupMembers($a->id, [1, 2], groupActor());

    expect($fake->groupEvents())->toHaveCount($count);
});

it('refuses invalid member lists and unknown ids on setGroupMembers', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1]), groupActor());

    expect(fn () => $fake->setGroupMembers($a->id, [2, 2], groupActor()))->toThrow(ContractException::class, 'nur einmal')
        ->and(fn () => $fake->setGroupMembers($a->id, [4], groupActor()))->toThrow(ContractException::class, 'anderen Kontakt')
        ->and(fn () => $fake->setGroupMembers($a->id, [99], groupActor()))->toThrow(SubscriptionNotFoundException::class)
        ->and(fn () => $fake->setGroupMembers(99, [], groupActor()))->toThrow(SubscriptionGroupNotFoundException::class)
        ->and($fake->findGroup($a->id)?->memberIds)->toBe([1]);
});

it('adds a member at a position, at the end, or moves an existing member', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1]), groupActor());

    expect($fake->addGroupMember($a->id, 2, null, groupActor())->memberIds)->toBe([1, 2])
        ->and($fake->addGroupMember($a->id, 3, 1, groupActor())->memberIds)->toBe([3, 1, 2])
        ->and($fake->addGroupMember($a->id, 3, 99, groupActor())->memberIds)->toBe([1, 2, 3])
        ->and($fake->find(3)?->groupPosition)->toBe(3);
});

it('refuses addGroupMember for foreign or unknown subscriptions and unknown groups', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A'), groupActor());

    expect(fn () => $fake->addGroupMember($a->id, 4, null, groupActor()))->toThrow(ContractException::class, 'anderen Kontakt')
        ->and(fn () => $fake->addGroupMember($a->id, 99, null, groupActor()))->toThrow(SubscriptionNotFoundException::class)
        ->and(fn () => $fake->addGroupMember(99, 1, null, groupActor()))->toThrow(SubscriptionGroupNotFoundException::class);
});

it('removes a member and refuses a non-member', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2]), groupActor());

    $result = $fake->removeGroupMember($a->id, 1, groupActor());

    expect($result->memberIds)->toBe([2])
        ->and($fake->find(1)?->groupId)->toBeNull()
        ->and($fake->find(2)?->groupPosition)->toBe(1)
        ->and(fn () => $fake->removeGroupMember($a->id, 3, groupActor()))->toThrow(ContractException::class, 'Die Leistung „Leistung 3“ gehört nicht zu dieser Gruppe.')
        ->and(fn () => $fake->removeGroupMember($a->id, 99, groupActor()))->toThrow(SubscriptionNotFoundException::class)
        ->and(fn () => $fake->removeGroupMember(99, 1, groupActor()))->toThrow(SubscriptionGroupNotFoundException::class);
});

it('dissolves a group, ungroups the members and renumbers the remaining groups', function () {
    $fake = fakeWithSubscriptions();
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2]), groupActor());
    $b = $fake->createGroup(new NewSubscriptionGroup(1, 'B'), groupActor());

    $fake->dissolveGroup($a->id, groupActor());

    $event = $fake->groupEvents()[array_key_last($fake->groupEvents())];

    expect($fake->findGroup($a->id))->toBeNull()
        ->and($fake->find(1)?->groupId)->toBeNull()
        ->and($fake->findGroup($b->id)?->position)->toBe(1)
        ->and($event['event'])->toBe('SubscriptionGroupDissolved')
        ->and($event['payload'])->toBe(['group_id' => $a->id, 'contact_id' => 1, 'former_member_ids' => [1, 2]])
        ->and($event['actor'])->toEqual(groupActor())
        ->and(fn () => $fake->dissolveGroup($a->id, groupActor()))->toThrow(SubscriptionGroupNotFoundException::class);
});

it('records created events with name and members', function () {
    $fake = fakeWithSubscriptions();
    $group = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [2, 1]), groupActor());

    expect($fake->groupEvents())->toHaveCount(1)
        ->and($fake->groupEvents()[0]['event'])->toBe('SubscriptionGroupCreated')
        ->and($fake->groupEvents()[0]['payload'])->toBe(['group_id' => $group->id, 'contact_id' => 1, 'name' => 'A', 'member_ids' => [2, 1]])
        ->and($fake->groupEvents()[0]['actor'])->toEqual(groupActor());
});

it('cancels all active members to the shared date and skips ended ones', function () {
    $fake = fakeWithSubscriptions();
    $fake->seed(seededSubscription(5, ['status' => SubscriptionStatus::Ended, 'endsOn' => '2026-03-31']));
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 5, 2]), groupActor());

    $cancelled = $fake->cancelGroup($a->id, '2026-12-31', groupActor());

    expect(array_map(fn ($s) => $s->id, $cancelled))->toBe([1, 2])
        ->and($cancelled[0]->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($cancelled[0]->endsOn)->toBe('2026-12-31')
        ->and($cancelled[0]->groupId)->toBe($a->id)
        ->and($fake->find(5)?->endsOn)->toBe('2026-03-31')
        ->and($fake->find(3)?->status)->toBe(SubscriptionStatus::Active)
        ->and($fake->actors())->toHaveCount(2);
});

it('refuses a group cancellation without a date, with a malformed date, or one a member rejects', function () {
    $fake = fakeWithSubscriptions();
    $fake->seedEndOptions(2, ['2026-12-31']);
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2]), groupActor());

    expect(fn () => $fake->cancelGroup($a->id, null, groupActor()))->toThrow(ContractException::class, 'Für eine Gruppe muss ein gemeinsames Enddatum gewählt werden.')
        ->and(fn () => $fake->cancelGroup($a->id, '31.12.2026', groupActor()))->toThrow(ContractException::class, 'endsOn')
        ->and(fn () => $fake->cancelGroup($a->id, '2026-11-30', groupActor()))->toThrow(ContractException::class, 'Die Gruppe kann nicht zum 30.11.2026 gekündigt werden: Leistung „Leistung 2“')
        ->and(fn () => $fake->cancelGroup(99, '2026-12-31', groupActor()))->toThrow(SubscriptionGroupNotFoundException::class)
        ->and($fake->find(1)?->status)->toBe(SubscriptionStatus::Active);
});

it('intersects the end options of the active members', function () {
    $fake = fakeWithSubscriptions();
    $fake->seedEndOptions(1, ['2026-12-31', '2026-09-30', '2027-03-31']);
    $fake->seedEndOptions(2, ['2026-09-30', '2027-03-31', '2027-06-30']);
    $a = $fake->createGroup(new NewSubscriptionGroup(1, 'A', memberIds: [1, 2, 3]), groupActor());

    expect($fake->groupEndOptions($a->id))->toBe(['2026-09-30', '2027-03-31'])
        ->and($fake->groupEndOptions($fake->createGroup(new NewSubscriptionGroup(1, 'leer'), groupActor())->id))->toBe([])
        ->and(fn () => $fake->groupEndOptions(99))->toThrow(SubscriptionGroupNotFoundException::class);
});

it('rejects a malformed end option on seeding', function () {
    $fake = fakeWithSubscriptions();

    $fake->seedEndOptions(1, ['soon']);
})->throws(ContractException::class);

it('seeds a group and fills the member fields on read', function () {
    $fake = fakeWithSubscriptions();
    $fake->seedGroup(new SubscriptionGroupData(5, 1, 'Seed', 1, true, null, [2, 1], SubscriptionGroupData::invoiceGroupIdFor(5), '2026-11-08T00:00:00+00:00'));

    expect($fake->find(2)?->groupId)->toBe(5)
        ->and($fake->find(1)?->groupPosition)->toBe(2)
        ->and($fake->createGroup(new NewSubscriptionGroup(1, 'Next'), groupActor())->id)->toBe(6)
        ->and($fake->groupsForContact(1)[1]->position)->toBe(2);
});

it('validates the group data value objects', function () {
    expect(fn () => new SubscriptionGroupData(1, 1, '', 1, true, null, [], 'x', 'y'))->toThrow(ContractException::class)
        ->and(fn () => new SubscriptionGroupData(1, 1, 'A', 0, true, null, [], 'x', 'y'))->toThrow(ContractException::class)
        ->and(fn () => new NewSubscriptionGroup(1, 'A', memberIds: [1 => 5]))->toThrow(ContractException::class)
        ->and((new SubscriptionGroupData(1, 1, 'A', 2, true, 'n', [3], 'u', 'c'))->toArray())->toBe([
            'id' => 1, 'contact_id' => 1, 'name' => 'A', 'position' => 2, 'collapse_on_invoice' => true,
            'note' => 'n', 'member_ids' => [3], 'invoice_group_id' => 'u', 'created_at' => 'c',
        ]);
});
