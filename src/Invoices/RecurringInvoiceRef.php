<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Reference to a recurring-invoice template. `url` is not part of this
 * reference; the core page is `/recurring-invoices/{id}/edit`.
 */
final readonly class RecurringInvoiceRef
{
    public function __construct(
        public int $id,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
