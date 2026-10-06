<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IngestedDocument;

it('converts to an array with snake_case keys', function () {
    $document = new IngestedDocument(
        id: 7,
        diskPath: 'documents/incoming-invoices/2026/10/abc123.pdf',
        sha256: 'abc123',
        duplicate: false,
        url: '/incoming-invoices/inbox/7',
    );

    expect($document->toArray())->toBe([
        'id' => 7,
        'disk_path' => 'documents/incoming-invoices/2026/10/abc123.pdf',
        'sha256' => 'abc123',
        'duplicate' => false,
        'url' => '/incoming-invoices/inbox/7',
    ]);
});

it('allows duplicate to be true', function () {
    $document = new IngestedDocument(
        id: 7,
        diskPath: 'documents/incoming-invoices/2026/10/abc123.pdf',
        sha256: 'abc123',
        duplicate: true,
        url: '/incoming-invoices/inbox/7',
    );

    expect($document->duplicate)->toBeTrue();
});
