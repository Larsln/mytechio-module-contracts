<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingDocumentRef;

it('allows a null incoming invoice id', function () {
    $ref = new IncomingDocumentRef(
        id: 7,
        source: 'paperless',
        status: 'extracting',
        diskPath: 'documents/incoming-invoices/2026/10/abc123.pdf',
        sha256: 'abc123',
        incomingInvoiceId: null,
        url: '/incoming-invoices/inbox/7',
    );

    expect($ref->toArray())->toBe([
        'id' => 7,
        'source' => 'paperless',
        'status' => 'extracting',
        'disk_path' => 'documents/incoming-invoices/2026/10/abc123.pdf',
        'sha256' => 'abc123',
        'incoming_invoice_id' => null,
        'url' => '/incoming-invoices/inbox/7',
    ]);
});

it('converts to an array with a linked incoming invoice', function () {
    $ref = new IncomingDocumentRef(
        id: 7,
        source: 'upload',
        status: 'imported',
        diskPath: 'documents/incoming-invoices/2026/10/abc123.pdf',
        sha256: 'abc123',
        incomingInvoiceId: 42,
        url: '/incoming-invoices/inbox/7',
    );

    expect($ref->toArray()['incoming_invoice_id'])->toBe(42);
});
