<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Ergebnis eines `IncomingDocuments::ingest()`-Aufrufs.
 *
 * `duplicate` ist `true`, wenn bereits ein lebender Posteingang-Beleg mit
 * demselben `sha256` existiert — in diesem Fall sind `id`/`diskPath`/`url`
 * die des bestehenden Belegs, es wird kein neuer angelegt.
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
