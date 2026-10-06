<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\IncomingInvoiceRef;
use MyTechIO\Contracts\Documents\IncomingInvoices;
use MyTechIO\Contracts\Invoices\PaidDocument;

/**
 * Test-Double für `IncomingInvoices`: `seed()` hinterlegt einen Beleg für
 * `find()`, `seedPaidDocument()` für `paidBetween()`.
 */
final class FakeIncomingInvoices implements IncomingInvoices
{
    /**
     * @var array<int, IncomingInvoiceRef>
     */
    private array $refs = [];

    /**
     * @var list<PaidDocument>
     */
    private array $paidDocuments = [];

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

    public function find(int $id): ?IncomingInvoiceRef
    {
        return $this->refs[$id] ?? null;
    }

    public function seed(IncomingInvoiceRef $ref): void
    {
        $this->refs[$ref->id] = $ref;
    }

    public function seedPaidDocument(PaidDocument $document): void
    {
        $this->paidDocuments[] = $document;
    }
}
