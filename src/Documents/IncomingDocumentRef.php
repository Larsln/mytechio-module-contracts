<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Reference to a document in the incoming-document inbox (Posteingang).
 *
 * `source` is the module's free-form key, or `upload` for core uploads;
 * `status` is the core-internal lifecycle status (e.g. `extracting`,
 * `manual`, `imported`) as a string; `incomingInvoiceId` is set once the
 * document has been matched to an incoming invoice (Eingangsrechnung),
 * otherwise `null`.
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
