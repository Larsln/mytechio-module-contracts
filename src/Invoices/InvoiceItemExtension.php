<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * A module's extension of an invoice line item.
 *
 * The core collects implementations via the container tag
 * `mytechio.invoice_item_extensions` (see `InvoiceItemExtensionRegistry`
 * in the core) and stores a JSON field `extras` per invoice line item, in
 * which each extension stores its own part under the key `key()` —
 * `key()` MUST match the module name so the core can filter out
 * extensions belonging to disabled modules.
 */
interface InvoiceItemExtension
{
    /**
     * Key under a line item's `extras`, e.g. `"customer_assets"`.
     * Matches the module name.
     */
    public function key(): string;

    /**
     * @param  array<string, mixed>  $extras  This extension's own part (extras[key()]).
     * @return array<string, string> Field => error message (empty = valid)
     */
    public function validate(array $extras, ?int $contactId): array;

    /**
     * Called after the line items have been (re-)created, within the core transaction.
     *
     * @param  list<array{id: int, extras: array<string, mixed>}>  $items  (extras = this extension's own part)
     */
    public function afterItemsSynced(int $invoiceId, ?int $contactId, array $items): void;

    /**
     * Additional lines shown under the line item description on the document.
     *
     * @param  array<string, mixed>  $extras
     * @return list<string>
     */
    public function annotate(int $invoiceItemId, array $extras): array;

    /**
     * This extension's own part of the extras for a document copy (duplication/cancellation
     * (Storno)): e.g. removing links.
     *
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    public function duplicate(array $extras): array;
}
