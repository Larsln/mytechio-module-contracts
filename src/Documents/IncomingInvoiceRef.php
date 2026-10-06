<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Lesender Verweis auf eine Eingangsrechnung, z. B. für den Profit-Split.
 *
 * `url` ist die relative Kern-URL der Eingangsrechnung.
 */
final readonly class IncomingInvoiceRef
{
    public function __construct(
        public int $id,
        public string $number,
        public string $status,
        public ?int $contactId,
        public string $url,
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
        ];
    }
}
