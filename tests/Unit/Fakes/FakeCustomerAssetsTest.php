<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\AssetNotFoundException;
use MyTechIO\Contracts\Assets\CustomerAssetData;
use MyTechIO\Contracts\Assets\NewCustomerAsset;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\Fakes\FakeCustomerAssets;

it('finds a seeded asset by id', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(
        id: 10,
        contactId: 1,
        type: 'domain',
        label: 'example.de',
        status: 'active',
        acquiredAt: '2026-01-01',
        cancelledAt: null,
        invoiceItemId: null,
        notes: null,
    ));

    expect($assets->find(10)?->label)->toBe('example.de')
        ->and($assets->find(999))->toBeNull();
});

it('filters forContact by contact and optional type', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(1, 1, 'domain', 'example.de', 'active', null, null, null, null));
    $assets->seed(new CustomerAssetData(2, 1, 'license', 'Office', 'active', null, null, null, null));
    $assets->seed(new CustomerAssetData(3, 2, 'domain', 'other.de', 'active', null, null, null, null));

    expect($assets->forContact(1))->toHaveCount(2)
        ->and($assets->forContact(1, 'domain'))->toHaveCount(1)
        ->and($assets->forContact(1, 'domain')[0]->label)->toBe('example.de');
});

it('sorts findByLabel matches with active assets first, then by id', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(1, 1, 'domain', 'example.de', 'cancelled', null, null, null, null));
    $assets->seed(new CustomerAssetData(2, 2, 'domain', 'example.de', 'active', null, null, null, null));
    $assets->seed(new CustomerAssetData(3, 3, 'domain', 'example.de', 'active', null, null, null, null));

    $matches = $assets->findByLabel('domain', 'example.de');

    expect(array_map(fn (CustomerAssetData $a) => $a->id, $matches))->toBe([2, 3, 1]);
});

it('creates a new active asset with an auto id', function () {
    $assets = new FakeCustomerAssets;

    $created = $assets->create(new NewCustomerAsset(contactId: 1, type: 'domain', label: 'example.de'));

    expect($created->id)->toBe(1)
        ->and($created->status)->toBe('active')
        ->and($assets->find(1))->toBe($created);
});

it('auto ids continue after a seeded id', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(5, 1, 'domain', 'example.de', 'active', null, null, null, null));

    $created = $assets->create(new NewCustomerAsset(contactId: 1, type: 'domain', label: 'other.de'));

    expect($created->id)->toBe(6);
});

it('cancels an existing asset and sets its status and cancelledAt', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(1, 1, 'domain', 'example.de', 'active', null, null, null, null));

    $cancelled = $assets->cancel(1, '2026-10-06');

    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->cancelledAt)->toBe('2026-10-06')
        ->and($assets->find(1)->status)->toBe('cancelled');
});

it('throws AssetNotFoundException when cancelling an unknown id', function () {
    $assets = new FakeCustomerAssets;

    expect(fn () => $assets->cancel(999))->toThrow(AssetNotFoundException::class);
});

it('finds an asset by source and external id', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(
        id: 1,
        contactId: 1,
        type: 'domain',
        label: 'example.de',
        status: 'active',
        acquiredAt: null,
        cancelledAt: null,
        invoiceItemId: null,
        notes: null,
        source: 'domainrobot',
        externalId: 'example.de',
    ));

    expect($assets->findBySource('domainrobot', 'example.de')?->id)->toBe(1)
        ->and($assets->findBySource('domainrobot', 'other.de'))->toBeNull()
        ->and($assets->findBySource('ionos', 'example.de'))->toBeNull();
});

it('creates a sourced asset with attributes', function () {
    $assets = new FakeCustomerAssets;

    $created = $assets->create(new NewCustomerAsset(
        contactId: 1,
        type: 'domain',
        label: 'example.de',
        source: 'domainrobot',
        externalId: 'example.de',
        renewsAt: '2027-01-01',
        autorenew: true,
        attributes: ['registrar' => 'kyberio'],
    ));

    expect($created->source)->toBe('domainrobot')
        ->and($created->externalId)->toBe('example.de')
        ->and($created->renewsAt)->toBe('2027-01-01')
        ->and($created->autorenew)->toBeTrue()
        ->and($created->attributes)->toBe(['registrar' => 'kyberio']);
});

it('attaches a source to a previously manual asset', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(1, 1, 'domain', 'example.de', 'active', null, null, null, null));

    $updated = $assets->attachSource(1, 'domainrobot', 'example.de');

    expect($updated->source)->toBe('domainrobot')
        ->and($updated->externalId)->toBe('example.de')
        ->and($assets->find(1)->source)->toBe('domainrobot');
});

it('throws AssetNotFoundException when attaching a source to an unknown id', function () {
    $assets = new FakeCustomerAssets;

    expect(fn () => $assets->attachSource(999, 'domainrobot', 'example.de'))->toThrow(AssetNotFoundException::class);
});

it('updates status, renewal, autorenew and merges attributes from a connector status', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(
        id: 1,
        contactId: 1,
        type: 'domain',
        label: 'example.de',
        status: 'active',
        acquiredAt: null,
        cancelledAt: null,
        invoiceItemId: null,
        notes: null,
        source: 'domainrobot',
        externalId: 'example.de',
        attributes: ['registrar' => 'kyberio'],
    ));

    $updated = $assets->updateFromSource(1, new ConnectorStatus(
        externalId: 'example.de',
        status: 'active',
        statusLabel: 'Aktiv',
        renewsAt: '2027-05-01',
        autorenew: false,
        locked: true,
        url: null,
        attributes: ['locked_reason' => 'transfer'],
    ));

    expect($updated->externalStatus)->toBe('active')
        ->and($updated->renewsAt)->toBe('2027-05-01')
        ->and($updated->autorenew)->toBeFalse()
        ->and($updated->attributes)->toBe(['registrar' => 'kyberio', 'locked_reason' => 'transfer'])
        ->and($updated->status)->toBe('active');
});

it('throws AssetNotFoundException when updating an unknown id from source', function () {
    $assets = new FakeCustomerAssets;

    expect(fn () => $assets->updateFromSource(999, new ConnectorStatus(
        externalId: 'example.de',
        status: 'active',
        statusLabel: null,
        renewsAt: null,
        autorenew: null,
        locked: null,
        url: null,
    )))->toThrow(AssetNotFoundException::class);
});

it('returns assets linked to an invoice item', function () {
    $assets = new FakeCustomerAssets;
    $assets->seed(new CustomerAssetData(1, 1, 'domain', 'example.de', 'active', null, null, 55, null));
    $assets->seed(new CustomerAssetData(2, 1, 'domain', 'other.de', 'active', null, null, null, null));

    $linked = $assets->forInvoiceItem(55);

    expect($linked)->toHaveCount(1)
        ->and($linked[0]->id)->toBe(1)
        ->and($assets->forInvoiceItem(999))->toBe([]);
});
