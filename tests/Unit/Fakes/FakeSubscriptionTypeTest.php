<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeSubscriptionType;
use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionField;
use MyTechIO\Contracts\Subscriptions\SubscriptionFieldKind;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;
use MyTechIO\Contracts\Subscriptions\SubscriptionType;

it('has sensible defaults', function () {
    $type = new FakeSubscriptionType;

    expect($type)->toBeInstanceOf(SubscriptionType::class)
        ->and($type->key())->toBe('fake')
        ->and($type->label())->toBe('Fake')
        ->and($type->fields())->toBe([])
        ->and($type->schemaVersion())->toBe(1)
        ->and($type->inputComponent())->toBeNull()
        ->and($type->singleObject())->toBeFalse()
        ->and($type->calls())->toBe([]);
});

it('can be configured as a single-object type', function () {
    expect((new FakeSubscriptionType(singleObject: true))->singleObject())->toBeTrue();
});

it('exposes the configured key, label, fields, schema version and input component', function () {
    $fields = [new SubscriptionField('registrar', 'Registrar', SubscriptionFieldKind::Text)];
    $type = new FakeSubscriptionType('domain', 'Domain', $fields, 3, 'DomainForm');

    expect($type->key())->toBe('domain')
        ->and($type->label())->toBe('Domain')
        ->and($type->fields())->toBe($fields)
        ->and($type->schemaVersion())->toBe(3)
        ->and($type->inputComponent())->toBe('DomainForm');
});

it('records every hook call in order', function () {
    $type = new FakeSubscriptionType;
    $first = new SubscriptionData(1, 2, 'fake', 'A', SubscriptionStatus::Active, null, null, '1', '2026-01-01');
    $second = new SubscriptionData(5, 2, 'fake', 'B', SubscriptionStatus::Active, null, null, '1', '2026-01-01');

    $type->created($first);
    $type->cancelled($first);
    $type->created($second);
    $type->ended($first);

    expect($type->calls())->toBe([
        ['hook' => 'created', 'id' => 1],
        ['hook' => 'cancelled', 'id' => 1],
        ['hook' => 'created', 'id' => 5],
        ['hook' => 'ended', 'id' => 1],
    ]);
});
