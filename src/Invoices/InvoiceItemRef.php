<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Reference to an invoice line item including its invoice.
 *
 * `invoiceNumber` is `null` as long as the invoice is in draft status — an
 * invoice number is only drawn from the number range upon finalization.
 * `url` is the invoice's relative core URL (e.g. `/invoices/12`).
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
