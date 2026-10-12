<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Read-only reference to an incoming invoice (Eingangsrechnung), e.g. for
 * the profit split.
 *
 * `url` is the core's relative URL for the incoming invoice. `items` are the
 * invoice's lines in position order, `invoiceDate`/`serviceDate` are `Y-m-d`
 * (`serviceDate` is the delivery or service date, `null` when the supplier
 * gave none), `supplierName` is the display name of the supplier (contact or
 * one-time supplier) and `cancelsIncomingInvoiceId` names the original
 * invoice when this reference is a cancellation document.
 */
final readonly class IncomingInvoiceRef
{
    /**
     * @param  list<IncomingInvoiceItemRef>  $items
     */
    public function __construct(
        public int $id,
        public string $number,
        public string $status,
        public ?int $contactId,
        public string $url,
        public array $items = [],
        public ?string $invoiceDate = null,
        public ?string $serviceDate = null,
        public ?string $supplierName = null,
        public ?int $cancelsIncomingInvoiceId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'contact_id' => $this->contactId,
            'url' => $this->url,
            'items' => array_map(static fn (IncomingInvoiceItemRef $item): array => $item->toArray(), $this->items),
            'invoice_date' => $this->invoiceDate,
            'service_date' => $this->serviceDate,
            'supplier_name' => $this->supplierName,
            'cancels_incoming_invoice_id' => $this->cancelsIncomingInvoiceId,
        ];
    }
}
