<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceItemRef;
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
        'items' => [],
        'invoice_date' => null,
        'service_date' => null,
        'supplier_name' => null,
        'cancels_incoming_invoice_id' => null,
    ]);
});

it('carries lines, dates, supplier name and the cancelled original', function () {
    $item = new IncomingInvoiceItemRef(31, 10, 1, 'Workstation', '1.000', '-2500.0000', '0650', null);
    $ref = new IncomingInvoiceRef(
        id: 10,
        number: 'ER-2026-0004',
        status: 'booked',
        contactId: null,
        url: '/incoming-invoices/10',
        items: [$item],
        invoiceDate: '2026-03-01',
        serviceDate: '2026-02-15',
        supplierName: 'Dell GmbH',
        cancelsIncomingInvoiceId: 9,
    );

    expect($ref->toArray()['items'])->toBe([$item->toArray()])
        ->and($ref->toArray()['invoice_date'])->toBe('2026-03-01')
        ->and($ref->toArray()['service_date'])->toBe('2026-02-15')
        ->and($ref->toArray()['supplier_name'])->toBe('Dell GmbH')
        ->and($ref->toArray()['cancels_incoming_invoice_id'])->toBe(9);
});
