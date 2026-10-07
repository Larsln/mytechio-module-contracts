<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Reference to a created invoice.
 *
 * `number` is `null` as long as the invoice is in draft status — an
 * invoice number is only drawn from the number range upon finalization.
 */
final readonly class InvoiceRef
{
    public function __construct(
        public int $id,
        public ?string $number,
        public string $status,
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
        ];
    }
}
