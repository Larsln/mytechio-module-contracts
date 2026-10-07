<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * AI extraction provider for incoming invoices, container tag
 * `mytechio.invoice_extractors`. The core selects exactly one driver via
 * configuration (`extractorKey()`); multiple installed modules merely
 * offer multiple drivers to choose from — never more than one runs per
 * extraction.
 */
interface InvoiceExtractor
{
    /**
     * Unique key of the driver == module name, e.g.
     * `"extraction-anthropic"`.
     */
    public function extractorKey(): string;

    /**
     * Display name for the module overview and the inbox hint, e.g.
     * `"Anthropic Claude"`.
     */
    public function label(): string;

    /**
     * Whether the driver is ready to use (e.g. API key configured). A
     * configured module can still be disabled — the core checks that
     * separately via the module registration.
     */
    public function configured(): bool;

    /**
     * @return array<string, mixed> raw payload (leaves supplier/invoice/items/totals plus meta.model) — validation is done by the core.
     *
     * @throws ExtractionFailedException
     */
    public function extract(string $pdfBlob, ExtractionContextData $context): array;
}
