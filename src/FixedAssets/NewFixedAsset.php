<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Input of `FixedAssets::create()`: adds one booked incoming-invoice line to
 * the inventory list (`ListOnly`, no accounting effect). Name, acquisition
 * date and cost come from the document — assets arise from source documents,
 * never from free input.
 */
final readonly class NewFixedAsset
{
    public function __construct(
        public int $incomingInvoiceItemId,
        public ?string $category = null,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'incoming_invoice_item_id' => $this->incomingInvoiceItemId,
            'category' => $this->category,
            'notes' => $this->notes,
        ];
    }
}
