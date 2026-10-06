<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Ergebnis einer erfolgreichen Ablage.
 *
 * `localPath` ist der Pfad auf dem GoBD-pflichtigen `local`-Disk (die
 * Quelle der Wahrheit), `sha256` der Hash des abgelegten Blobs,
 * `paperlessId` — falls vorhanden — die Dokument-ID im asynchronen
 * Paperless-Zweit-Viewer.
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
