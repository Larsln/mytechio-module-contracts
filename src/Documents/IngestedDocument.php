<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Result of an `IncomingDocuments::ingest()` call.
 *
 * `duplicate` is `true` when a live incoming-document already exists with
 * the same `sha256` — in that case `id`/`diskPath`/`url` refer to the
 * existing document, and no new one is created.
 */
final readonly class IngestedDocument
{
    public function __construct(
        public int $id,
        public string $diskPath,
        public string $sha256,
        public bool $duplicate,
        public string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'disk_path' => $this->diskPath,
            'sha256' => $this->sha256,
            'duplicate' => $this->duplicate,
            'url' => $this->url,
        ];
    }
}
