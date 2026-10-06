<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\NewCustomerAsset;

it('defaults acquiredAt and notes to null', function () {
    $asset = new NewCustomerAsset(contactId: 2, type: 'domain', label: 'example.de');

    expect($asset->acquiredAt)->toBeNull()
        ->and($asset->notes)->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    $asset = new NewCustomerAsset(contactId: 2, type: 'domain', label: 'example.de', acquiredAt: '2026-01-01', notes: 'Notiz');

    expect($asset->toArray())->toBe([
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'acquired_at' => '2026-01-01',
        'notes' => 'Notiz',
    ]);
});
