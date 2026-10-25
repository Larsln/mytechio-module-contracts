<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceItemRef;
use MyTechIO\Contracts\Invoices\InvoiceRef;
use MyTechIO\Contracts\Invoices\Invoices;
use MyTechIO\Contracts\Invoices\PaidDocument;

/**
 * Test double for `Invoices`: remembers every draft, always returns
 * status `'draft'` and no invoice number — matching the core semantics.
 * `find()` serves the created drafts and the invoices seeded via
 * `seedInvoice()`.
 */
final class FakeInvoices implements Invoices
{
    /**
     * @var array<int, InvoiceDraft>
     */
    private array $drafts = [];

    /**
     * @var array<int, InvoiceRef>
     */
    private array $invoices = [];

    /**
     * @var array<int, InvoiceItemRef>
     */
    private array $items = [];

    /**
     * @var list<PaidDocument>
     */
    private array $paidDocuments = [];

    private int $nextId = 1;

    public function createDraft(InvoiceDraft $draft): InvoiceRef
    {
        $id = $this->nextId++;
        $this->drafts[$id] = $draft;
        $this->invoices[$id] = new InvoiceRef(id: $id, number: null, status: 'draft');

        return $this->invoices[$id];
    }

    public function find(int $invoiceId): ?InvoiceRef
    {
        return $this->invoices[$invoiceId] ?? null;
    }

    public function seedInvoice(InvoiceRef $invoice): void
    {
        $this->invoices[$invoice->id] = $invoice;
    }

    public function findItem(int $invoiceItemId): ?InvoiceItemRef
    {
        return $this->items[$invoiceItemId] ?? null;
    }

    public function seedItem(InvoiceItemRef $item): void
    {
        $this->items[$item->id] = $item;
    }

    /**
     * @return list<PaidDocument>
     */
    public function paidBetween(string $from, string $to): array
    {
        $matches = array_values(array_filter(
            $this->paidDocuments,
            fn (PaidDocument $document): bool => $document->paidOn >= $from && $document->paidOn <= $to,
        ));

        usort($matches, fn (PaidDocument $a, PaidDocument $b) => $a->paidOn <=> $b->paidOn);

        return $matches;
    }

    public function seedPaidDocument(PaidDocument $document): void
    {
        $this->paidDocuments[] = $document;
    }

    /**
     * @return array<int, InvoiceDraft>
     */
    public function drafts(): array
    {
        return $this->drafts;
    }
}
