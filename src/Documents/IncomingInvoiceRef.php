<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Read-only reference to an incoming invoice (Eingangsrechnung), e.g. for
 * the profit split.
 *
 * `url` is the core's relative URL for the incoming invoice.
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
