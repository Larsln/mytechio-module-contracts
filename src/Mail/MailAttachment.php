<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Ein Anhang einer modulinitiierten Mail.
 *
 * `path` liegt auf dem `local`-Disk oder ist absolut — die Implementierung
 * entscheidet, wie sie ihn auflöst; der Fake legt den Anhang nur ab, ohne
 * ihn zu lesen.
 */
final readonly class MailAttachment
{
    public function __construct(
        public string $name,
        public string $path,
        public ?string $mimeType = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'mime_type' => $this->mimeType,
        ];
    }
}
