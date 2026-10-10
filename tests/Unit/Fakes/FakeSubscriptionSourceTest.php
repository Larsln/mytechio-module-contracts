<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeSubscriptionSource;
use MyTechIO\Contracts\Subscriptions\SubscriptionSource;
use MyTechIO\Contracts\Subscriptions\SubscriptionSuggestion;

it('has the configured key and types', function () {
    $source = new FakeSubscriptionSource(key: 'domainrobot', types: ['domain', 'certificate']);

    expect($source)->toBeInstanceOf(SubscriptionSource::class)
        ->and($source->sourceKey())->toBe('domainrobot')
        ->and($source->subscriptionTypes())->toBe(['domain', 'certificate']);
});

it('has no capabilities by default and throws on actions', function () {
    $source = new FakeSubscriptionSource;

    expect($source->capabilities())->toBe([]);

    $source->setAutorenew('a.example', false, new ActorRef(1, 'Admin'));
})->throws(ContractException::class, 'Aktion wird von dieser Quelle nicht unterstützt.');

it('throws for an action whose capability is not listed', function () {
    $source = new FakeSubscriptionSource(capabilities: ['autorenew']);

    $source->releaseTransfer('a.example', new ActorRef(1, 'Admin'));
})->throws(ContractException::class);

it('records supported actions and returns the configured state', function () {
    $actor = new ActorRef(1, 'Admin');
    $source = (new FakeSubscriptionSource(key: 'domainrobot', capabilities: ['autorenew', 'owner_change', 'transfer_out']))
        ->actionState('queued');

    $ref = $source->setAutorenew('a.example', false, $actor);
    $source->transferToCompany('a.example', $actor);
    $source->releaseTransfer('a.example', $actor);

    expect($ref->state)->toBe('queued')
        ->and($ref->sourceKey)->toBe('domainrobot')
        ->and($ref->jobReference)->not->toBeNull()
        ->and($source->calls())->toHaveCount(3)
        ->and($source->calls()[0]['enabled'])->toBeFalse()
        ->and($source->calls()[1]['action'])->toBe('transfer_to_company')
        ->and($source->calls()[2]['action'])->toBe('release_transfer')
        ->and($source->calls()[2]['actor'])->toBe($actor);
});

it('answers done without a job reference by default', function () {
    $source = (new FakeSubscriptionSource)->withCapabilities(['autorenew']);

    $ref = $source->setAutorenew('a.example', true, new ActorRef(null, 'job'));

    expect($ref->state)->toBe('done')
        ->and($ref->jobReference)->toBeNull()
        ->and($ref->action)->toBe('set_autorenew');
});

it('suggests by type and query with a limit', function () {
    $source = new FakeSubscriptionSource;
    $source->seedSuggestion(new SubscriptionSuggestion('fake', 'domain', 'a.example', 'a.example'));
    $source->seedSuggestion(new SubscriptionSuggestion('fake', 'domain', 'b.example', 'B.example'));
    $source->seedSuggestion(new SubscriptionSuggestion('fake', 'domain', 'c.example', 'c.example'));

    expect($source->suggest('domain', 'b', 20))->toHaveCount(1)
        ->and($source->suggest('domain', '', 2))->toHaveCount(2)
        ->and($source->suggest('license', '', 20))->toBe([]);
});
