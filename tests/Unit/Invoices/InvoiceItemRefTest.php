<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\ItemComponentRef;
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
        'position' => null,
        'article_id' => null,
        'quantity' => null,
        'unit' => null,
        'net_amount' => null,
        'components' => [],
    ]);
});

it('carries the components of a package line', function () {
    $ref = new InvoiceItemRef(7, 12, 'RE-1', 'finalized', 'Paket', '/invoices/12', components: [
        new ItemComponentRef(9, 'ART-0009', 'Domain', '2.0000', 'Stk', 'example.de'),
    ]);

    expect($ref->toArray()['components'])->toBe([[
        'article_id' => 9,
        'article_number' => 'ART-0009',
        'name' => 'Domain',
        'quantity' => '2.0000',
        'unit' => 'Stk',
        'object' => 'example.de',
    ]]);
});
