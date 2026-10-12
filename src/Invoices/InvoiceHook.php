<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\HookWarning;

/**
 * A module's hook into the lifecycle of outgoing invoices.
 *
 * The core collects implementations via the container tag
 * `mytechio.invoice_hooks` and calls every hook of an enabled, compatible
 * module synchronously, INSIDE the core's database transaction of the action:
 * what a hook writes is committed or rolled back together with the invoice,
 * and an exception from it rolls the whole action back. Hooks of a module that
 * is disabled or incompatible are skipped. The references are complete
 * (`InvoiceRef::$items`, `orderId`, `type`).
 */
interface InvoiceHook
{
    /**
     * An invoice was finalized (real invoice, number drawn, journal entry
     * posted); also called when a proforma invoice becomes a real invoice.
     * Invoices generated from an order (`orderId` set) have already moved their
     * goods at order completion. Remarks are returned to the user as warnings;
     * they never block the finalization.
     *
     * @return list<HookWarning>
     *
     * @throws ContractException to refuse the finalization.
     */
    public function afterFinalized(InvoiceRef $invoice, ActorRef $actor): array;

    /**
     * An invoice was cancelled: `$cancellation` is the issued cancellation
     * invoice, `$original` the cancelled invoice.
     */
    public function afterCancelled(InvoiceRef $original, InvoiceRef $cancellation, ActorRef $actor): void;
}
