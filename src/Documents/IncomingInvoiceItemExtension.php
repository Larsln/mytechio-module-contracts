<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * A module's extension of an incoming-invoice line item — the counterpart of
 * `Invoices\InvoiceItemExtension` for supplier invoices.
 *
 * The core collects implementations via the container tag
 * `mytechio.incoming_invoice_item_extensions` (`IncomingInvoiceItemExtensionRegistry`
 * in the core) and stores a JSON field `extras` per line item, in which each
 * extension keeps its own part under the key `key()` — `key()` MUST match the
 * module name so the core can filter out extensions of disabled modules. The
 * module draws its input on the capture form through the slot
 * `incoming_invoice.item.editor`; lifecycle effects (what happens when the
 * invoice is booked or cancelled) belong to `IncomingInvoiceGuard`.
 */
interface IncomingInvoiceItemExtension
{
    /**
     * Key under a line item's `extras`, e.g. `"fixed-assets"`. Matches the
     * module name.
     */
    public function key(): string;

    /**
     * @param  array<string, mixed>  $extras  This extension's own part (extras[key()]).
     * @param  ?int  $supplierContactId  The invoice's supplier contact, `null` for a one-time supplier.
     * @return array<string, string> Field => error message (empty = valid)
     */
    public function validate(array $extras, ?int $supplierContactId): array;

    /**
     * Called after the line items have been (re-)created, within the core
     * transaction.
     *
     * @param  list<array{id: int, extras: array<string, mixed>}>  $items  (extras = this extension's own part)
     */
    public function afterItemsSynced(int $invoiceId, array $items): void;

    /**
     * Additional lines shown under the line item description on the document.
     *
     * @param  array<string, mixed>  $extras
     * @return list<string>
     */
    public function annotate(int $incomingInvoiceItemId, array $extras): array;
}
