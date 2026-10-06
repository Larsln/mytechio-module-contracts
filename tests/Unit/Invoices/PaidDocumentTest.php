<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\PaidDocument;

it('converts to an array with a nullable contact id', function () {
    $document = new PaidDocument(
        type: 'invoice',
        id: 12,
        number: 'RE-2026-0012',
        paidOn: '2026-09-15',
        netAmount: '119.0000',
        contactId: 3,
        url: '/invoices/12',
    );

    expect($document->toArray())->toBe([
        'type' => 'invoice',
        'id' => 12,
        'number' => 'RE-2026-0012',
        'paid_on' => '2026-09-15',
        'net_amount' => '119.0000',
        'contact_id' => 3,
        'url' => '/invoices/12',
    ]);
});
