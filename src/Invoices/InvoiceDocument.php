<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/** The issued paper of an invoice: the file's bytes, its MIME type and a file name. */
final readonly class InvoiceDocument
{
    public function __construct(
        public string $content,
        public string $mimeType,
        public string $filename,
    ) {}
}
