<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Anlegen von Rechnungs-Entwürfen aus Modulen heraus.
 *
 * Kern-Semantik: liefert immer eine Rechnung im Status Draft zurück —
 * Finalisierung, Versand und Storno bleiben ausschließlich Sache des
 * Kern-UI und sind über diesen Vertrag nicht erreichbar.
 */
interface Invoices
{
    public function createDraft(InvoiceDraft $draft): InvoiceRef;

    /**
     * Liefert den Verweis auf eine Rechnungsposition (inkl. ihrer Rechnung)
     * anhand ihrer ID, oder `null`, wenn keine Position mit dieser ID existiert.
     */
    public function findItem(int $invoiceItemId): ?InvoiceItemRef;
}
