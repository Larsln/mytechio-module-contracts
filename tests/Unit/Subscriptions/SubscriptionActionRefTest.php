<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionActionRef;

it('converts to an array with snake_case keys', function () {
    $ref = new SubscriptionActionRef('domainrobot', 'example.de', 'set_autorenew', 'job-1', 'queued');

    expect($ref->toArray())->toBe([
        'source_key' => 'domainrobot',
        'external_id' => 'example.de',
        'action' => 'set_autorenew',
        'job_reference' => 'job-1',
        'state' => 'queued',
    ]);
});

it('accepts the three known states', function (string $state) {
    expect((new SubscriptionActionRef('s', 'e', 'release_transfer', null, $state))->state)->toBe($state);
})->with(['queued', 'done', 'unsupported']);

it('rejects an unknown state', function () {
    new SubscriptionActionRef('s', 'e', 'set_autorenew', null, 'running');
})->throws(ContractException::class, "state must be 'queued', 'done' or 'unsupported'");
