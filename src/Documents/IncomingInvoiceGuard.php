<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * A module's hook into the lifecycle of incoming invoices.
 *
 * The core collects implementations via the container tag
 * `mytechio.incoming_invoice_guards` (see `IncomingInvoiceGuardRegistry` in
 * the core) and calls every guard of an enabled, compatible module at four
 * points. Guards of a module that is disabled or incompatible are skipped.
 *
 * A `before*` hook vetoes the action by throwing a `ContractException`
 * with a German, user-facing message; nothing has been changed yet when it
 * runs (booking: before the internal number is drawn). An `after*` hook runs
 * INSIDE the core's database transaction: whatever it writes (register rows,
 * journal references) is committed or rolled back together with the booking
 * or the cancellation, and an exception from it rolls the whole action back.
 */
interface IncomingInvoiceGuard
{
    /**
     * Called while a draft is being booked, after the core's own checks and
     * before the internal number is drawn.
     *
     * @throws ContractException to refuse the booking.
     */
    public function beforeBook(IncomingInvoiceRef $invoice): void;

    /**
     * Called inside the booking transaction after the journal entry has been
     * posted. `$invoice` is the booked invoice with its internal number.
     */
    public function afterBooked(IncomingInvoiceRef $invoice, ActorRef $actor): void;

    /**
     * Called before a booked invoice is cancelled, before anything is changed.
     *
     * @throws ContractException to refuse the cancellation.
     */
    public function beforeCancel(IncomingInvoiceRef $invoice): void;

    /**
     * Called inside the cancellation transaction after the cancellation
     * document has been created and its journal counter-entry has been posted.
     */
    public function afterCancelled(IncomingInvoiceRef $original, IncomingInvoiceRef $cancellation, ActorRef $actor): void;

    /**
     * IDs of the invoice's line items that this module owns. The core refuses
     * to move such a line to another expense account or to link it to an
     * article, because the module's own records point at it.
     *
     * @return list<int>
     */
    public function lockedItemIds(int $invoiceId): array;
}
