<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Result of a successful storage operation.
 *
 * `localPath` is the path on the GoBD-compliant `local` disk (the source
 * of truth), `sha256` is the hash of the stored blob, `paperlessId` — if
 * present — is the document ID in the asynchronous Paperless secondary
 * viewer.
 */
final readonly class StoredDocument
{
    public function __construct(
        public string $localPath,
        public string $sha256,
        public ?int $paperlessId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'local_path' => $this->localPath,
            'sha256' => $this->sha256,
            'paperless_id' => $this->paperlessId,
        ];
    }
}
