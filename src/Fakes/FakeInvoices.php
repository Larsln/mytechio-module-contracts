<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceRef;
use MyTechIO\Contracts\Invoices\Invoices;

/**
 * Test-Double für `Invoices`: merkt sich jeden Entwurf, liefert immer den
 * Status `'draft'` und keine Rechnungsnummer — wie die Kern-Semantik.
 */
final class FakeInvoices implements Invoices
{
    /**
     * @var array<int, InvoiceDraft>
     */
    private array $drafts = [];

    private int $nextId = 1;

    public function createDraft(InvoiceDraft $draft): InvoiceRef
    {
        $id = $this->nextId++;
        $this->drafts[$id] = $draft;

        return new InvoiceRef(id: $id, number: null, status: 'draft');
    }

    /**
     * @return array<int, InvoiceDraft>
     */
    public function drafts(): array
    {
        return $this->drafts;
    }
}
