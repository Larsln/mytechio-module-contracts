<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\Invoices\PaidDocument;

/**
 * Read-only access to incoming invoices (Eingangsrechnungen), e.g. for the
 * profit split — modules do not create or change incoming invoices
 * through this contract.
 */
interface IncomingInvoices
{
    /**
     * Returns all incoming invoices paid within the period (inclusive),
     * sorted by `paidOn`. `type` is always `"incoming_invoice"` for every
     * entry.
     *
     * @return list<PaidDocument>
     */
    public function paidBetween(string $from, string $to): array;

    /**
     * Returns the reference to an incoming invoice by its ID, or `null` if
     * no incoming invoice exists with that ID.
     */
    public function find(int $id): ?IncomingInvoiceRef;
}
