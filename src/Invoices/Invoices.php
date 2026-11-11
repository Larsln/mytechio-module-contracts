<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * Invoices for modules: drafts, the invoice of an order and (since 2.19)
 * issuing and sending it the way the core's own action does.
 *
 * `createDraft()` always returns a draft. Cancellation (Storno) remains
 * exclusively the responsibility of the core UI and is not reachable
 * through this contract.
 */
interface Invoices
{
    public function createDraft(InvoiceDraft $draft): InvoiceRef;

    /**
     * Returns the complete reference to an invoice by its ID (drafts
     * included), or `null` if no invoice with this ID exists.
     */
    public function find(int $invoiceId): ?InvoiceRef;

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

    /**
     * Draft invoice of a confirmed or completed order with the order's lines,
     * totals and tax carried over (since 2.19). `paymentMethod`
     * (`bank_transfer`, `direct_debit`, `card`) overrides the contact's
     * default. An order that already has an invoice that is not cancelled
     * returns that one instead of a second.
     *
     * @throws ContractException when the order is unknown, a draft or cancelled, or the payment method is unknown
     */
    public function createFromOrder(int $orderId, ActorRef $actor, ?string $paymentMethod = null): InvoiceRef;

    /**
     * Issues a draft (number, document, booking) and mails it to the
     * contact's invoice address with the core's template, exactly like the
     * core's "issue and send" (since 2.19). It picks up where it stands: an
     * issued invoice that was not sent is only sent, a sent one is returned
     * as it is. A tax treatment that needs a person's confirmation is only
     * issued with `$taxReviewConfirmed`.
     *
     * @throws ContractException when the invoice is unknown or cancelled, the contact has no address to mail to,
     *                           the tax treatment needs the confirmation or the core refuses the issuing; when
     *                           only the sending failed the invoice stays issued and a repeat sends it
     */
    public function issueAndSend(int $invoiceId, ActorRef $actor, bool $taxReviewConfirmed = false): InvoiceRef;

    /**
     * The issued papers of an order (since 2.20): its invoice and what
     * followed it (cancellation invoices), oldest first, each as complete as
     * `find()` returns it. Drafts and proformas are not papers yet and are
     * left out.
     *
     * @return list<InvoiceRef>
     */
    public function forOrder(int $orderId): array;

    /**
     * The archived PDF of an issued invoice (since 2.20), the very file the
     * customer was sent. `null` for an unknown invoice and for a draft or
     * proforma, which has no binding paper yet. Whoever hands it on decides
     * who may see it; the contract checks no permission.
     */
    public function document(int $invoiceId): ?InvoiceDocument;
}
