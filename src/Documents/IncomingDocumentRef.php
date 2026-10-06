<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Verweis auf einen Posteingang-Beleg.
 *
 * `source` ist der freie Modulschlüssel bzw. `upload` für Kern-Uploads,
 * `status` der Kern-interne Lebenszyklus-Status (z. B. `extracting`,
 * `manual`, `imported`) als String, `incomingInvoiceId` ist gesetzt,
 * sobald der Beleg einer Eingangsrechnung zugeordnet wurde, sonst `null`.
 */
final readonly class IncomingDocumentRef
{
    public function __construct(
        public int $id,
        public string $source,
        public string $status,
        public string $diskPath,
        public string $sha256,
        public ?int $incomingInvoiceId,
        public string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'status' => $this->status,
            'disk_path' => $this->diskPath,
            'sha256' => $this->sha256,
            'incoming_invoice_id' => $this->incomingInvoiceId,
            'url' => $this->url,
        ];
    }
}
