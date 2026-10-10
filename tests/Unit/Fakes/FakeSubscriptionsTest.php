<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeSubscriptions;
use MyTechIO\Contracts\Subscriptions\NewSubscription;
use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionNotFoundException;
use MyTechIO\Contracts\Subscriptions\Subscriptions;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;

/**
 * @param  array<string, mixed>  $overrides
 */
function seededSubscription(int $id, array $overrides = []): SubscriptionData
{
    return new SubscriptionData(...[
        'id' => $id,
        'contactId' => 1,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => SubscriptionStatus::Active,
        'articleId' => null,
        'priceOptionId' => null,
        'quantity' => '1',
        'startsOn' => '2026-01-01',
        ...$overrides,
    ]);
}

function connectorStatus(?string $renewsAt = null, ?bool $autorenew = null): ConnectorStatus
{
    return new ConnectorStatus('example.de', 'active', 'Aktiv', $renewsAt, $autorenew, null, null, ['locked_reason' => 'transfer']);
}

it('implements the Subscriptions contract and finds a seeded subscription by id', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(10));

    expect($subscriptions)->toBeInstanceOf(Subscriptions::class)
        ->and($subscriptions->find(10)?->label)->toBe('example.de')
        ->and($subscriptions->find(999))->toBeNull();
});

it('filters forContact by contact and optional type, ordered by id', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(3));
    $subscriptions->seed(seededSubscription(1, ['type' => 'license', 'label' => 'Office']));
    $subscriptions->seed(seededSubscription(2, ['contactId' => 2]));

    $all = $subscriptions->forContact(1);
    $domains = $subscriptions->forContact(1, 'domain');

    expect(array_map(fn (SubscriptionData $s) => $s->id, $all))->toBe([1, 3])
        ->and(array_map(fn (SubscriptionData $s) => $s->id, $domains))->toBe([3]);
});

it('sorts findByLabel matches with active subscriptions first, then by id', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, ['status' => SubscriptionStatus::Cancelled]));
    $subscriptions->seed(seededSubscription(2, ['contactId' => 2]));
    $subscriptions->seed(seededSubscription(3, ['contactId' => 3]));
    $subscriptions->seed(seededSubscription(4, ['label' => 'other.de']));

    $matches = $subscriptions->findByLabel('domain', 'example.de');

    expect(array_map(fn (SubscriptionData $s) => $s->id, $matches))->toBe([2, 3, 1]);
});

it('finds a subscription by source and external id', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, ['source' => 'domainrobot', 'externalId' => 'example.de']));

    expect($subscriptions->findBySource('domainrobot', 'example.de')?->id)->toBe(1)
        ->and($subscriptions->findBySource('domainrobot', 'other.de'))->toBeNull()
        ->and($subscriptions->findBySource('ionos', 'example.de'))->toBeNull();
});

it('finds the subscription linked to an invoice item', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1));
    $subscriptions->linkInvoiceItem(55, 1);

    expect($subscriptions->forInvoiceItem(55)?->id)->toBe(1)
        ->and($subscriptions->forInvoiceItem(999))->toBeNull();
});

it('throws SubscriptionNotFoundException when linking an invoice item to an unknown subscription', function () {
    (new FakeSubscriptions)->linkInvoiceItem(55, 999);
})->throws(SubscriptionNotFoundException::class);

it('creates an active subscription with an auto id, today as default start and the actor recorded', function () {
    $subscriptions = new FakeSubscriptions;
    $actor = new ActorRef(7, 'Admin');

    $created = $subscriptions->create(new NewSubscription(
        contactId: 1,
        type: 'domain',
        label: 'example.de',
        articleId: 9,
        priceOptionId: 4,
        quantity: '2',
        source: 'domainrobot',
        externalId: 'example.de',
        renewsOn: '2027-01-01',
        autorenew: true,
        attributes: ['registrar' => 'kyberio'],
    ), $actor);

    expect($created->id)->toBe(1)
        ->and($created->status)->toBe(SubscriptionStatus::Active)
        ->and($created->startsOn)->toBe(date('Y-m-d'))
        ->and($created->articleId)->toBe(9)
        ->and($created->priceOptionId)->toBe(4)
        ->and($created->quantity)->toBe('2')
        ->and($created->source)->toBe('domainrobot')
        ->and($created->externalId)->toBe('example.de')
        ->and($created->renewsOn)->toBe('2027-01-01')
        ->and($created->autorenew)->toBeTrue()
        ->and($created->attributes)->toBe(['registrar' => 'kyberio'])
        ->and($subscriptions->find(1))->toBe($created)
        ->and($subscriptions->actors())->toBe([['action' => 'create', 'id' => 1, 'actor' => $actor]]);
});

it('uses the given start date when creating', function () {
    $created = (new FakeSubscriptions)->create(
        new NewSubscription(contactId: 1, type: 'generic', label: 'Wartung', startsOn: '2026-11-01'),
        new ActorRef(null, 'job'),
    );

    expect($created->startsOn)->toBe('2026-11-01');
});

it('continues auto ids after a seeded id', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(5));

    $created = $subscriptions->create(new NewSubscription(contactId: 1, type: 'domain', label: 'other.de'), new ActorRef(null, 'job'));

    expect($created->id)->toBe(6);
});

it('attaches a source to a previously manual subscription', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, ['providerName' => 'IONOS']));

    $updated = $subscriptions->attachSource(1, 'domainrobot', 'example.de');

    expect($updated->source)->toBe('domainrobot')
        ->and($updated->externalId)->toBe('example.de')
        ->and($updated->providerName)->toBe('IONOS')
        ->and($subscriptions->find(1)->source)->toBe('domainrobot');
});

it('throws SubscriptionNotFoundException when attaching a source to an unknown id', function () {
    (new FakeSubscriptions)->attachSource(999, 'domainrobot', 'example.de');
})->throws(SubscriptionNotFoundException::class);

it('applies a connector status: renewal date part, autorenew, external status, merged attributes', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, [
        'source' => 'domainrobot',
        'externalId' => 'example.de',
        'attributes' => ['registrar' => 'kyberio'],
    ]));

    $updated = $subscriptions->updateFromSource(1, connectorStatus('2027-05-01T00:00:00+00:00', false));

    expect($updated->renewsOn)->toBe('2027-05-01')
        ->and($updated->autorenew)->toBeFalse()
        ->and($updated->externalStatus)->toBe('active')
        ->and($updated->attributes)->toBe(['registrar' => 'kyberio', 'locked_reason' => 'transfer'])
        ->and($updated->status)->toBe(SubscriptionStatus::Active)
        ->and($subscriptions->find(1))->toBe($updated);
});

it('accepts a plain date as renewal date and a missing renewal', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1));

    expect($subscriptions->updateFromSource(1, connectorStatus('2027-05-01'))->renewsOn)->toBe('2027-05-01')
        ->and($subscriptions->updateFromSource(1, connectorStatus(null))->renewsOn)->toBeNull();
});

it('throws SubscriptionNotFoundException when updating an unknown id from source', function () {
    (new FakeSubscriptions)->updateFromSource(999, connectorStatus());
})->throws(SubscriptionNotFoundException::class);

it('cancels with the given end date, today as cancellation date and the actor recorded', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1));
    $actor = new ActorRef(7, 'Admin');

    $cancelled = $subscriptions->cancel(1, '2026-12-31', $actor);

    expect($cancelled->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($cancelled->cancelledOn)->toBe(date('Y-m-d'))
        ->and($cancelled->endsOn)->toBe('2026-12-31')
        ->and($subscriptions->find(1))->toBe($cancelled)
        ->and($subscriptions->actors())->toBe([['action' => 'cancel', 'id' => 1, 'actor' => $actor]]);
});

it('ends a cancellation without a date on the renewal date, or today without one', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, ['renewsOn' => '2027-01-01']));
    $subscriptions->seed(seededSubscription(2));
    $actor = new ActorRef(null, 'job');

    expect($subscriptions->cancel(1, null, $actor)->endsOn)->toBe('2027-01-01')
        ->and($subscriptions->cancel(2, null, $actor)->endsOn)->toBe(date('Y-m-d'));
});

it('throws SubscriptionNotFoundException when cancelling an unknown id', function () {
    (new FakeSubscriptions)->cancel(999, null, new ActorRef(null, 'job'));
})->throws(SubscriptionNotFoundException::class);

it('throws ContractException when cancelling an ended subscription and records no actor', function () {
    $subscriptions = new FakeSubscriptions;
    $subscriptions->seed(seededSubscription(1, ['status' => SubscriptionStatus::Ended, 'endsOn' => '2026-06-30']));

    try {
        $subscriptions->cancel(1, null, new ActorRef(null, 'job'));
    } catch (ContractException $exception) {
        expect($exception->getMessage())->toBe('Leistung mit ID 1 ist bereits beendet.')
            ->and($subscriptions->actors())->toBe([])
            ->and($subscriptions->find(1)->status)->toBe(SubscriptionStatus::Ended);

        return;
    }

    $this->fail('Expected a ContractException.');
});
