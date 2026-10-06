<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\DocumentMeta;
use MyTechIO\Contracts\Documents\DocumentStore;
use MyTechIO\Contracts\Documents\StoredDocument;

/**
 * Test-Double für `DocumentStore`: legt nichts wirklich ab, liefert aber
 * deterministische Pfade/Hashes und merkt sich jeden Aufruf für Assertions
 * in Modul-Tests.
 */
final class FakeDocumentStore implements DocumentStore
{
    /**
     * @var list<array{blob: string, directory: string, filename: string, meta: DocumentMeta, result: StoredDocument}>
     */
    private array $calls = [];

    private int $nextPaperlessId = 1;

    public function store(string $blob, string $directory, string $filename, DocumentMeta $meta): StoredDocument
    {
        $result = new StoredDocument(
            localPath: rtrim($directory, '/').'/'.$filename,
            sha256: hash('sha256', $blob),
            paperlessId: $this->nextPaperlessId++,
        );

        $this->calls[] = [
            'blob' => $blob,
            'directory' => $directory,
            'filename' => $filename,
            'meta' => $meta,
            'result' => $result,
        ];

        return $result;
    }

    /**
     * @return list<array{blob: string, directory: string, filename: string, meta: DocumentMeta, result: StoredDocument}>
     */
    public function stored(): array
    {
        return $this->calls;
    }
}
