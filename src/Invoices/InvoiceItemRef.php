<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Verweis auf eine Rechnungsposition inklusive ihrer Rechnung.
 *
 * `invoiceNumber` ist `null`, solange die Rechnung im Status Draft ist —
 * eine Rechnungsnummer wird erst beim Finalisieren aus dem Nummernkreis
 * gezogen. `url` ist die relative Kern-URL der Rechnung (z. B. `/invoices/12`).
 */
final readonly class InvoiceItemRef
{
    public function __construct(
        public int $id,
        public int $invoiceId,
        public ?string $invoiceNumber,
        public string $invoiceStatus,
        public string $description,
        public string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'invoice_status' => $this->invoiceStatus,
            'description' => $this->description,
            'url' => $this->url,
        ];
    }
}
