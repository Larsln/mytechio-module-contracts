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
     * Every extracted leaf (head fields and per-line-item fields) carries
     * `value`, optionally `source_text` and `page` (1-based) and optionally
     * `source_box`: the smallest rectangle enclosing the source text on that
     * page, as normalised page coordinates in `[0, 1]` with the origin at
     * the top left (`{"x0": 0.62, "y0": 0.41, "x1": 0.83, "y1": 0.43}`,
     * `x0 < x1`, `y0 < y1`). The box is best-effort: drivers omit it when
     * unsure, and the core cross-checks it against the PDF text layer and
     * ignores absent or invalid boxes.
     *
     * @return array<string, mixed> raw payload (leaves supplier/invoice/items/totals plus meta.model) — validation is done by the core.
     *
     * @throws ExtractionFailedException
     */
    public function extract(string $pdfBlob, ExtractionContextData $context): array;
}
