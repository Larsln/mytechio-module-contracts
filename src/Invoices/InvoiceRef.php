<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Reference to an invoice.
 *
 * `number` is `null` as long as the invoice is in draft status — an
 * invoice number is only drawn from the number range upon finalization.
 *
 * `createDraft()` returns the short form (`id`, `number`, `status`). The
 * references the core hands to an `InvoiceHook` are complete: `type`
 * (`invoice`, `cancellation`, `credit_note`, ...), `contactId`, the dates
 * (`Y-m-d`), `orderId` (set when the invoice was generated from an order, so
 * it moved no stock itself), `cancelsInvoiceId` (set on a cancellation
 * invoice), the `items` in position order and the `paymentMethod`
 * (`bank_transfer`, `direct_debit` or, since 2.19, `card`).
 *
 * `find()` additionally fills the money and dating fields: `grossTotal`
 * (decimal string, 4 places), `openAmount` (gross total minus what is paid,
 * `'0.0000'` once paid), the ISO 4217 `currency`, the `dueDate` (`Y-m-d`)
 * and `sentAt` (ISO-8601, `null` until the invoice was sent).
 */
final readonly class InvoiceRef
{
    /**
     * @param  list<InvoiceItemRef>  $items
     */
    public function __construct(
        public int $id,
        public ?string $number,
        public string $status,
        public ?string $type = null,
        public ?int $contactId = null,
        public ?string $invoiceDate = null,
        public ?string $deliveryDate = null,
        public ?int $orderId = null,
        public ?int $cancelsInvoiceId = null,
        public array $items = [],
        public ?string $paymentMethod = null,
        public ?string $grossTotal = null,
        public ?string $openAmount = null,
        public ?string $currency = null,
        public ?string $dueDate = null,
        public ?string $sentAt = null,
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
            'type' => $this->type,
            'contact_id' => $this->contactId,
            'invoice_date' => $this->invoiceDate,
            'delivery_date' => $this->deliveryDate,
            'order_id' => $this->orderId,
            'cancels_invoice_id' => $this->cancelsInvoiceId,
            'items' => array_map(static fn (InvoiceItemRef $item): array => $item->toArray(), $this->items),
            'payment_method' => $this->paymentMethod,
            'gross_total' => $this->grossTotal,
            'open_amount' => $this->openAmount,
            'currency' => $this->currency,
            'due_date' => $this->dueDate,
            'sent_at' => $this->sentAt,
        ];
    }
}
