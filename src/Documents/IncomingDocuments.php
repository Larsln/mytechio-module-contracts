<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Incoming-document inbox ingest (Posteingang) for modules.
 *
 * Core semantics: the local GoBD-compliant storage and the inbox belong to
 * the core — modules only submit the raw content of a document through
 * this contract (e.g. from a pull from Paperless-NGX). A successful
 * `ingest()` creates the document in the core storage and fires the core
 * event `DocumentStored`.
 */
interface IncomingDocuments
{
    /**
     * Creates a document in the inbox. A hash duplicate (same `sha256` of
     * the blob as an already existing document) does not create a new
     * document, but returns the existing one with `duplicate = true` —
     * this allows a module to repeat a pull without creating duplicates.
     *
     * @param  string  $blob  Raw file content (binary).
     * @param  string  $source  Free-form module key for the source (e.g. `paperless`); `upload` is reserved for the core.
     */
    public function ingest(string $blob, string $filename, string $source, IngestOptions $options): IngestedDocument;

    /**
     * Returns the reference to a document in the inbox by its ID, or
     * `null` if none exists with that ID.
     */
    public function find(int $id): ?IncomingDocumentRef;
}
