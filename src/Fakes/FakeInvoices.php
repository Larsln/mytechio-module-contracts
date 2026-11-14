<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\InvoiceDocument;
use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceItemRef;
use MyTechIO\Contracts\Invoices\InvoiceRef;
use MyTechIO\Contracts\Invoices\Invoices;
use MyTechIO\Contracts\Invoices\PaidDocument;

/**
 * Test double for `Invoices`: remembers every draft, always returns
 * status `'draft'` and no invoice number — matching the core semantics.
 * `find()` serves the created drafts and the invoices seeded via
 * `seedInvoice()`. `createFromOrder()` makes one draft per order and
 * `issueAndSend()` numbers it `RE-FAKE-…` and marks it sent (`sent()`);
 * `failNextIssue()` and `failNextSend()` play a core that refuses.
 * `forOrder()` lists the issued invoices of an order and `document()`
 * returns a made-up PDF for each (or the one given to `seedDocument()`).
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

    private ?string $issueFailure = null;

    private ?string $sendFailure = null;

    /** @var list<int> */
    private array $sent = [];

    /** @var array<int, InvoiceDocument> */
    private array $documents = [];

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

    public function createFromOrder(int $orderId, ActorRef $actor, ?string $paymentMethod = null): InvoiceRef
    {
        if ($paymentMethod !== null && ! in_array($paymentMethod, ['bank_transfer', 'direct_debit', 'card'], true)) {
            throw new ContractException("Unbekannte Zahlart: {$paymentMethod}.");
        }

        foreach ($this->invoices as $invoice) {
            if ($invoice->orderId === $orderId && $invoice->status !== 'cancelled') {
                return $invoice;
            }
        }

        $id = $this->nextId++;

        return $this->invoices[$id] = new InvoiceRef(id: $id, number: null, status: 'draft', type: 'invoice', orderId: $orderId, paymentMethod: $paymentMethod ?? 'bank_transfer');
    }

    public function issueAndSend(int $invoiceId, ActorRef $actor, bool $taxReviewConfirmed = false): InvoiceRef
    {
        $invoice = $this->invoices[$invoiceId] ?? throw new ContractException("Rechnung {$invoiceId} wurde nicht gefunden.");

        if ($invoice->status === 'cancelled') {
            throw new ContractException('Die Rechnung ist storniert.');
        }

        if ($invoice->status === 'draft') {
            if ($this->issueFailure !== null) {
                $message = $this->issueFailure;
                $this->issueFailure = null;

                throw new ContractException($message);
            }

            $invoice = $this->invoices[$invoiceId] = $this->with($invoice, number: sprintf('RE-FAKE-%05d', $invoiceId), status: 'issued');
        }

        if ($invoice->sentAt !== null) {
            return $invoice;
        }

        if ($this->sendFailure !== null) {
            $message = $this->sendFailure;
            $this->sendFailure = null;

            throw new ContractException($message);
        }

        $this->sent[] = $invoiceId;

        return $this->invoices[$invoiceId] = $this->with($invoice, number: $invoice->number, status: 'awaiting_payment', sentAt: '2030-01-01T00:00:00+00:00');
    }

    /** The next issuing of a draft is refused with this message; the invoice stays a draft. */
    public function failNextIssue(string $message = 'Die Rechnung kann nicht ausgestellt werden.'): void
    {
        $this->issueFailure = $message;
    }

    /** The next sending fails with this message; the invoice stays issued. */
    public function failNextSend(string $message = 'Die Rechnung konnte nicht verschickt werden.'): void
    {
        $this->sendFailure = $message;
    }

    /**
     * @return list<int> ids of the invoices `issueAndSend()` mailed, in order
     */
    public function sent(): array
    {
        return $this->sent;
    }

    public function forOrder(int $orderId): array
    {
        return array_values(array_filter(
            $this->invoices,
            static fn (InvoiceRef $invoice): bool => $invoice->orderId === $orderId && ! in_array($invoice->status, ['draft', 'proforma'], true),
        ));
    }

    public function document(int $invoiceId): ?InvoiceDocument
    {
        $invoice = $this->invoices[$invoiceId] ?? null;

        if ($invoice === null || in_array($invoice->status, ['draft', 'proforma'], true)) {
            return null;
        }

        return $this->documents[$invoiceId] ?? new InvoiceDocument("%PDF-FAKE {$invoice->number}", 'application/pdf', ($invoice->number ?? "Rechnung-{$invoiceId}").'.pdf');
    }

    /** The paper `document()` returns for this invoice instead of the made-up one. */
    public function seedDocument(int $invoiceId, InvoiceDocument $document): void
    {
        $this->documents[$invoiceId] = $document;
    }

    private function with(InvoiceRef $invoice, ?string $number, string $status, ?string $sentAt = null): InvoiceRef
    {
        return new InvoiceRef(
            $invoice->id, $number, $status, $invoice->type, $invoice->contactId, $invoice->invoiceDate, $invoice->deliveryDate, $invoice->orderId,
            $invoice->cancelsInvoiceId, $invoice->items, $invoice->paymentMethod, $invoice->grossTotal, $invoice->openAmount, $invoice->currency,
            $invoice->dueDate, $sentAt ?? $invoice->sentAt,
        );
    }
}
