<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Verweis auf eine angelegte Rechnung.
 *
 * `number` ist `null`, solange die Rechnung im Status Draft ist — eine
 * Rechnungsnummer wird erst beim Finalisieren aus dem Nummernkreis gezogen.
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
