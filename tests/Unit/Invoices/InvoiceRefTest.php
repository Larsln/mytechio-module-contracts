<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\InvoiceItemRef;
use MyTechIO\Contracts\Invoices\InvoiceRef;

it('allows a null number for drafts', function () {
    $ref = new InvoiceRef(id: 1, number: null, status: 'draft');

    expect($ref->toArray())->toBe([
        'id' => 1,
        'number' => null,
        'status' => 'draft',
        'type' => null,
        'contact_id' => null,
        'invoice_date' => null,
        'delivery_date' => null,
        'order_id' => null,
        'cancels_invoice_id' => null,
        'items' => [],
    ]);
});

it('carries the lines and links the hooks need', function () {
    $ref = new InvoiceRef(
        id: 3,
        number: 'RE-2026-00003',
        status: 'issued',
        type: 'invoice',
        contactId: 8,
        invoiceDate: '2026-02-01',
        deliveryDate: '2026-01-30',
        orderId: 5,
        items: [new InvoiceItemRef(9, 3, 'RE-2026-00003', 'issued', 'Kabel', '/invoices/3', 1, 7, '2.0000', 'Stk', '20.0000')],
    );

    expect($ref->toArray()['order_id'])->toBe(5)
        ->and($ref->toArray()['items'][0])->toMatchArray(['article_id' => 7, 'quantity' => '2.0000', 'net_amount' => '20.0000', 'position' => 1, 'unit' => 'Stk']);
});
