<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Read-only reference to a document (outgoing or incoming invoice) paid
 * within the period, for the profit split.
 *
 * `type` is `"invoice"` (from `Invoices::paidBetween()`) or
 * `"incoming_invoice"` (from `Documents\IncomingInvoices::paidBetween()`);
 * `netAmount` is the net amount with 4 decimal places, `url` is the
 * document's relative core URL.
 */
final readonly class PaidDocument
{
    public function __construct(
        public string $type,
        public int $id,
        public string $number,
        public string $paidOn,
        public string $netAmount,
        public ?int $contactId,
        public string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'number' => $this->number,
            'paid_on' => $this->paidOn,
            'net_amount' => $this->netAmount,
            'contact_id' => $this->contactId,
            'url' => $this->url,
        ];
    }
}
