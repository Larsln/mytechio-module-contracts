<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\StoredDocument;

it('converts to an array with snake_case keys', function () {
    $document = new StoredDocument(localPath: 'documents/invoices/2026/10/foo.pdf', sha256: 'abc123', paperlessId: 7);

    expect($document->toArray())->toBe([
        'local_path' => 'documents/invoices/2026/10/foo.pdf',
        'sha256' => 'abc123',
        'paperless_id' => 7,
    ]);
});

it('allows a null paperless id', function () {
    $document = new StoredDocument(localPath: 'documents/invoices/2026/10/foo.pdf', sha256: 'abc123', paperlessId: null);

    expect($document->toArray()['paperless_id'])->toBeNull();
});
