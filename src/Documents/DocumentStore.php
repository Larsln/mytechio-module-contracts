<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Storage for arbitrary documents (receipts, PDFs, imports) for modules.
 *
 * Core semantics: the local GoBD-compliant storage (GoBD: Germany's
 * record-keeping retention rules) is the owner of the document,
 * Paperless-NGX is an asynchronous secondary viewer — a failure in
 * Paperless must not cause `store()` to fail. After successful storage,
 * the implementation fires the core event `DocumentStored`.
 * Modules get no direct access to Paperless or the filesystem through
 * this contract.
 */
interface DocumentStore
{
    /**
     * @param  string  $blob  Raw file content (binary).
     * @param  string  $directory  Logical target directory, relative to the core's storage convention (e.g. `documents/invoices/2026/10`).
     * @param  string  $filename  File name including extension.
     */
    public function store(string $blob, string $directory, string $filename, DocumentMeta $meta): StoredDocument;
}
