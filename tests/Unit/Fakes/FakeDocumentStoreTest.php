<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\DocumentMeta;
use MyTechIO\Contracts\Fakes\FakeDocumentStore;

it('returns a deterministic path and hash and records the call', function () {
    $store = new FakeDocumentStore;
    $meta = new DocumentMeta(ownerType: 'invoice', ownerId: 1, kind: 'invoice_pdf');

    $result = $store->store('%PDF-1.4 content', 'documents/invoices/2026/10', 'rechnung.pdf', $meta);

    expect($result->localPath)->toBe('documents/invoices/2026/10/rechnung.pdf')
        ->and($result->sha256)->toBe(hash('sha256', '%PDF-1.4 content'))
        ->and($result->paperlessId)->toBe(1);

    expect($store->stored())->toHaveCount(1)
        ->and($store->stored()[0]['meta'])->toBe($meta);
});

it('assigns ascending paperless ids per call', function () {
    $store = new FakeDocumentStore;
    $meta = new DocumentMeta(ownerType: null, ownerId: null, kind: 'misc');

    $first = $store->store('a', 'dir', 'a.txt', $meta);
    $second = $store->store('b', 'dir', 'b.txt', $meta);

    expect($first->paperlessId)->toBe(1)
        ->and($second->paperlessId)->toBe(2);
});
