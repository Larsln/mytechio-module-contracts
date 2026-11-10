<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\Documents\ItemComponentRef;

/**
 * Reference to an invoice line item including its invoice.
 *
 * `invoiceNumber` is `null` as long as the invoice is in draft status — an
 * invoice number is only drawn from the number range upon finalization.
 * `url` is the invoice's relative core URL (e.g. `/invoices/12`).
 *
 * The appended fields describe the line itself and are filled when the
 * reference is part of an `InvoiceRef` handed to an `InvoiceHook`: `position`,
 * the linked `articleId`, `quantity` (decimal string), `unit` code and the
 * line's `netAmount` (4 decimals, negative on cancellation invoices).
 * `components` lists the components of a package line (see `ItemComponentRef`),
 * empty for other lines.
 */
final readonly class InvoiceItemRef
{
    /**
     * @param  list<ItemComponentRef>  $components
     */
    public function __construct(
        public int $id,
        public int $invoiceId,
        public ?string $invoiceNumber,
        public string $invoiceStatus,
        public string $description,
        public string $url,
        public ?int $position = null,
        public ?int $articleId = null,
        public ?string $quantity = null,
        public ?string $unit = null,
        public ?string $netAmount = null,
        public array $components = [],
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
            'position' => $this->position,
            'article_id' => $this->articleId,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'net_amount' => $this->netAmount,
            'components' => array_map(static fn (ItemComponentRef $component): array => $component->toArray(), $this->components),
        ];
    }
}
