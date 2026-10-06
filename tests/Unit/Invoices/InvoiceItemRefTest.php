<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\InvoiceItemRef;

it('allows a null invoice number for drafts', function () {
    $ref = new InvoiceItemRef(
        id: 7,
        invoiceId: 12,
        invoiceNumber: null,
        invoiceStatus: 'draft',
        description: 'Domain example.com',
        url: '/invoices/12',
    );

    expect($ref->toArray())->toBe([
        'id' => 7,
        'invoice_id' => 12,
        'invoice_number' => null,
        'invoice_status' => 'draft',
        'description' => 'Domain example.com',
        'url' => '/invoices/12',
    ]);
});
