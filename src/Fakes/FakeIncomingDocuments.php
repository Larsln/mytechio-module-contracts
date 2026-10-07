<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\IncomingDocumentRef;
use MyTechIO\Contracts\Documents\IncomingDocuments;
use MyTechIO\Contracts\Documents\IngestedDocument;
use MyTechIO\Contracts\Documents\IngestOptions;

/**
 * Test double for `IncomingDocuments`: doesn't actually store anything, but
 * detects duplicates via the blob's `sha256` like the core implementation,
 * assigns auto IDs, and returns deterministic paths/URLs.
 */
final class FakeIncomingDocuments implements IncomingDocuments
{
    /**
     * @var array<int, IncomingDocumentRef>
     */
    private array $documents = [];

    /**
     * @var list<array{blob: string, filename: string, source: string, options: IngestOptions, result: IngestedDocument}>
     */
    private array $calls = [];

    private int $nextId = 1;

    public function ingest(string $blob, string $filename, string $source, IngestOptions $options): IngestedDocument
    {
        $sha256 = hash('sha256', $blob);

        $existing = $this->findBySha256($sha256);

        if ($existing !== null) {
            $result = new IngestedDocument(
                id: $existing->id,
                diskPath: $existing->diskPath,
                sha256: $existing->sha256,
                duplicate: true,
                url: $existing->url,
            );
        } else {
            $id = $this->nextId++;
            $diskPath = sprintf('documents/incoming-invoices/%s/%s/%s.pdf', date('Y'), date('m'), $sha256);
            $url = "/incoming-invoices/inbox/{$id}";

            $this->documents[$id] = new IncomingDocumentRef(
                id: $id,
                source: $source,
                status: 'extracting',
                diskPath: $diskPath,
                sha256: $sha256,
                incomingInvoiceId: null,
                url: $url,
            );

            $result = new IngestedDocument(
                id: $id,
                diskPath: $diskPath,
                sha256: $sha256,
                duplicate: false,
                url: $url,
            );
        }

        $this->calls[] = [
            'blob' => $blob,
            'filename' => $filename,
            'source' => $source,
            'options' => $options,
            'result' => $result,
        ];

        return $result;
    }

    public function find(int $id): ?IncomingDocumentRef
    {
        return $this->documents[$id] ?? null;
    }

    /**
     * Stores an incoming document (Posteingang) directly in the in-memory double, e.g.
     * to prepare a document already linked to or imported as an incoming invoice
     * for `find()`, without going through `ingest()`.
     */
    public function seed(IncomingDocumentRef $document): void
    {
        $this->documents[$document->id] = $document;

        if ($document->id >= $this->nextId) {
            $this->nextId = $document->id + 1;
        }
    }

    /**
     * @return list<array{blob: string, filename: string, source: string, options: IngestOptions, result: IngestedDocument}>
     */
    public function ingested(): array
    {
        return $this->calls;
    }

    private function findBySha256(string $sha256): ?IncomingDocumentRef
    {
        foreach ($this->documents as $document) {
            if ($document->sha256 === $sha256) {
                return $document;
            }
        }

        return null;
    }
}
