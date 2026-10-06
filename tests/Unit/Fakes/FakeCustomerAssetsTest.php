<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\AssetNotFoundException;
use MyTechIO\Contracts\Assets\CustomerAssetData;
use MyTechIO\Contracts\Assets\NewCustomerAsset;
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
