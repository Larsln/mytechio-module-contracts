<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\Invoices\PaidDocument;

/**
 * Rein lesender Zugriff auf Eingangsrechnungen, z. B. für den
 * Profit-Split — Module legen/ändern keine Eingangsrechnungen über diesen
 * Vertrag.
 */
interface IncomingInvoices
{
    /**
     * Liefert alle im Zeitraum (inklusive) bezahlten Eingangsrechnungen,
     * sortiert nach `paidOn`. `type` ist bei jedem Eintrag stets
     * `"incoming_invoice"`.
     *
     * @return list<PaidDocument>
     */
    public function paidBetween(string $from, string $to): array;

    /**
     * Liefert den Verweis auf eine Eingangsrechnung anhand ihrer ID, oder
     * `null`, wenn keine Eingangsrechnung mit dieser ID existiert.
     */
    public function find(int $id): ?IncomingInvoiceRef;
}
