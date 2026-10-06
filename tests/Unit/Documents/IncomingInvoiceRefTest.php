<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceRef;

it('converts to an array with a nullable contact id', function () {
    $ref = new IncomingInvoiceRef(
        id: 9,
        number: 'ER-2026-0003',
        status: 'booked',
        contactId: 4,
        url: '/incoming-invoices/9',
    );

    expect($ref->toArray())->toBe([
        'id' => 9,
        'number' => 'ER-2026-0003',
        'status' => 'booked',
        'contact_id' => 4,
        'url' => '/incoming-invoices/9',
    ]);
});
