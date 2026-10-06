<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\CustomerAssetData;

it('converts to an array with snake_case keys', function () {
    $asset = new CustomerAssetData(
        id: 1,
        contactId: 2,
        type: 'domain',
        label: 'example.de',
        status: 'active',
        acquiredAt: '2026-01-01',
        cancelledAt: null,
        invoiceItemId: 55,
        notes: 'Primärdomain',
    );

    expect($asset->toArray())->toBe([
        'id' => 1,
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => 'active',
        'acquired_at' => '2026-01-01',
        'cancelled_at' => null,
        'invoice_item_id' => 55,
        'notes' => 'Primärdomain',
        'source' => null,
        'external_id' => null,
        'provider_name' => null,
        'renews_at' => null,
        'autorenew' => null,
        'external_status' => null,
        'attributes' => [],
    ]);
});

it('converts a sourced asset with attributes to an array', function () {
    $asset = new CustomerAssetData(
        id: 1,
        contactId: 2,
        type: 'domain',
        label: 'example.de',
        status: 'active',
        acquiredAt: '2026-01-01',
        cancelledAt: null,
        invoiceItemId: 55,
        notes: null,
        source: 'domainrobot',
        externalId: 'example.de',
        providerName: null,
        renewsAt: '2027-01-01',
        autorenew: true,
        externalStatus: 'active',
        attributes: ['registrar' => 'kyberio'],
    );

    expect($asset->toArray())->toBe([
        'id' => 1,
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => 'active',
        'acquired_at' => '2026-01-01',
        'cancelled_at' => null,
        'invoice_item_id' => 55,
        'notes' => null,
        'source' => 'domainrobot',
        'external_id' => 'example.de',
        'provider_name' => null,
        'renews_at' => '2027-01-01',
        'autorenew' => true,
        'external_status' => 'active',
        'attributes' => ['registrar' => 'kyberio'],
    ]);
});
