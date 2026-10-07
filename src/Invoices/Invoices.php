<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Creation of invoice drafts from modules.
 *
 * Core semantics: always returns an invoice in draft status — finalization,
 * sending, and cancellation (Storno) remain exclusively the responsibility
 * of the core UI and are not reachable through this contract.
 */
interface Invoices
{
    public function createDraft(InvoiceDraft $draft): InvoiceRef;

    /**
     * Returns the reference to an invoice line item (including its invoice)
     * by its ID, or `null` if no line item with this ID exists.
     */
    public function findItem(int $invoiceItemId): ?InvoiceItemRef;

    /**
     * Returns all outgoing invoices paid within the period (inclusive),
     * sorted by `paidOn`, e.g. for the profit split. `type` is always
     * `"invoice"` for every entry.
     *
     * @return list<PaidDocument>
     */
    public function paidBetween(string $from, string $to): array;
}
