<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\NewCustomerAsset;

it('defaults acquiredAt, notes and source fields to null', function () {
    $asset = new NewCustomerAsset(contactId: 2, type: 'domain', label: 'example.de');

    expect($asset->acquiredAt)->toBeNull()
        ->and($asset->notes)->toBeNull()
        ->and($asset->source)->toBeNull()
        ->and($asset->externalId)->toBeNull()
        ->and($asset->providerName)->toBeNull()
        ->and($asset->renewsAt)->toBeNull()
        ->and($asset->autorenew)->toBeNull()
        ->and($asset->attributes)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $asset = new NewCustomerAsset(contactId: 2, type: 'domain', label: 'example.de', acquiredAt: '2026-01-01', notes: 'Notiz');

    expect($asset->toArray())->toBe([
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'acquired_at' => '2026-01-01',
        'notes' => 'Notiz',
        'source' => null,
        'external_id' => null,
        'provider_name' => null,
        'renews_at' => null,
        'autorenew' => null,
        'attributes' => [],
    ]);
});

it('converts a sourced asset to an array', function () {
    $asset = new NewCustomerAsset(
        contactId: 2,
        type: 'domain',
        label: 'example.de',
        source: 'domainrobot',
        externalId: 'example.de',
        providerName: null,
        renewsAt: '2027-01-01',
        autorenew: false,
        attributes: ['registrar' => 'kyberio'],
    );

    expect($asset->toArray())->toBe([
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'acquired_at' => null,
        'notes' => null,
        'source' => 'domainrobot',
        'external_id' => 'example.de',
        'provider_name' => null,
        'renews_at' => '2027-01-01',
        'autorenew' => false,
        'attributes' => ['registrar' => 'kyberio'],
    ]);
});
