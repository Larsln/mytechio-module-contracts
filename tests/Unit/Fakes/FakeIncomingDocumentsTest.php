<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingDocumentRef;
use MyTechIO\Contracts\Documents\IngestOptions;
use MyTechIO\Contracts\Fakes\FakeIncomingDocuments;

it('ingests a new document with a deterministic disk path and url', function () {
    $documents = new FakeIncomingDocuments;

    $result = $documents->ingest('%PDF-1.4 content', 'rechnung.pdf', 'paperless', new IngestOptions);

    expect($result->id)->toBe(1)
        ->and($result->sha256)->toBe(hash('sha256', '%PDF-1.4 content'))
        ->and($result->diskPath)->toBe(sprintf('documents/incoming-invoices/%s/%s/%s.pdf', date('Y'), date('m'), $result->sha256))
        ->and($result->duplicate)->toBeFalse()
        ->and($result->url)->toBe('/incoming-invoices/inbox/1');
});

it('assigns ascending ids per call', function () {
    $documents = new FakeIncomingDocuments;

    $first = $documents->ingest('a', 'a.pdf', 'upload', new IngestOptions);
    $second = $documents->ingest('b', 'b.pdf', 'upload', new IngestOptions);

    expect($first->id)->toBe(1)
        ->and($second->id)->toBe(2);
});

it('detects a hash duplicate and returns the existing document', function () {
    $documents = new FakeIncomingDocuments;

    $first = $documents->ingest('%PDF-1.4 content', 'rechnung.pdf', 'upload', new IngestOptions);
    $second = $documents->ingest('%PDF-1.4 content', 'duplicate.pdf', 'paperless', new IngestOptions);

    expect($second->duplicate)->toBeTrue()
        ->and($second->id)->toBe($first->id)
        ->and($second->diskPath)->toBe($first->diskPath)
        ->and($second->url)->toBe($first->url);

    // Die Dublette erzeugt keinen zweiten Beleg.
    $next = $documents->ingest('other content', 'c.pdf', 'upload', new IngestOptions);
    expect($next->id)->toBe(2);
});

it('records every ingest call', function () {
    $documents = new FakeIncomingDocuments;
    $options = new IngestOptions(actorId: 5);

    $documents->ingest('content', 'a.pdf', 'upload', $options);

    expect($documents->ingested())->toHaveCount(1)
        ->and($documents->ingested()[0]['filename'])->toBe('a.pdf')
        ->and($documents->ingested()[0]['source'])->toBe('upload')
        ->and($documents->ingested()[0]['options'])->toBe($options);
});

it('finds an ingested document by id', function () {
    $documents = new FakeIncomingDocuments;
    $ingested = $documents->ingest('content', 'a.pdf', 'paperless', new IngestOptions);

    $ref = $documents->find($ingested->id);

    expect($ref)->toBeInstanceOf(IncomingDocumentRef::class)
        ->and($ref->id)->toBe($ingested->id)
        ->and($ref->source)->toBe('paperless')
        ->and($ref->sha256)->toBe($ingested->sha256)
        ->and($ref->incomingInvoiceId)->toBeNull();
});

it('returns null for an unknown id', function () {
    $documents = new FakeIncomingDocuments;

    expect($documents->find(999))->toBeNull();
});

it('allows seeding a document directly for find()', function () {
    $documents = new FakeIncomingDocuments;
    $seeded = new IncomingDocumentRef(
        id: 42,
        source: 'upload',
        status: 'imported',
        diskPath: 'documents/incoming-invoices/2026/10/abc123.pdf',
        sha256: 'abc123',
        incomingInvoiceId: 7,
        url: '/incoming-invoices/inbox/42',
    );

    $documents->seed($seeded);

    expect($documents->find(42))->toBe($seeded);

    // Die Auto-ID springt über die gesäte ID hinaus.
    $next = $documents->ingest('content', 'a.pdf', 'upload', new IngestOptions);
    expect($next->id)->toBe(43);
});
