<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Documents\IncomingInvoiceGuard;
use MyTechIO\Contracts\Documents\IncomingInvoiceRef;

/**
 * Test double for `IncomingInvoiceGuard`: records every call
 * (`beforeBookCalls`, `afterBookedCalls`, `beforeCancelCalls`,
 * `afterCancelledCalls`) so module tests can verify what the core passed in.
 * `vetoBooking()` and `vetoCancellation()` make the matching `before*` hook
 * throw a `ContractException`; `withLockedItemIds()` configures
 * `lockedItemIds()` (empty by default).
 */
final class FakeIncomingInvoiceGuard implements IncomingInvoiceGuard
{
    /**
     * @var list<IncomingInvoiceRef>
     */
    public array $beforeBookCalls = [];

    /**
     * @var list<array{invoice: IncomingInvoiceRef, actor: ActorRef}>
     */
    public array $afterBookedCalls = [];

    /**
     * @var list<IncomingInvoiceRef>
     */
    public array $beforeCancelCalls = [];

    /**
     * @var list<array{original: IncomingInvoiceRef, cancellation: IncomingInvoiceRef, actor: ActorRef}>
     */
    public array $afterCancelledCalls = [];

    private ?string $bookingVeto = null;

    private ?string $cancellationVeto = null;

    /**
     * @var list<int>
     */
    private array $lockedItemIds = [];

    public function beforeBook(IncomingInvoiceRef $invoice): void
    {
        $this->beforeBookCalls[] = $invoice;

        if ($this->bookingVeto !== null) {
            throw new ContractException($this->bookingVeto);
        }
    }

    public function afterBooked(IncomingInvoiceRef $invoice, ActorRef $actor): void
    {
        $this->afterBookedCalls[] = ['invoice' => $invoice, 'actor' => $actor];
    }

    public function beforeCancel(IncomingInvoiceRef $invoice): void
    {
        $this->beforeCancelCalls[] = $invoice;

        if ($this->cancellationVeto !== null) {
            throw new ContractException($this->cancellationVeto);
        }
    }

    public function afterCancelled(IncomingInvoiceRef $original, IncomingInvoiceRef $cancellation, ActorRef $actor): void
    {
        $this->afterCancelledCalls[] = ['original' => $original, 'cancellation' => $cancellation, 'actor' => $actor];
    }

    public function lockedItemIds(int $invoiceId): array
    {
        return $this->lockedItemIds;
    }

    public function vetoBooking(string $message): void
    {
        $this->bookingVeto = $message;
    }

    public function vetoCancellation(string $message): void
    {
        $this->cancellationVeto = $message;
    }

    /**
     * @param  list<int>  $itemIds
     */
    public function withLockedItemIds(array $itemIds): void
    {
        $this->lockedItemIds = $itemIds;
    }
}
