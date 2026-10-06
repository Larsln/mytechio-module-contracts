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
    ]);
});
