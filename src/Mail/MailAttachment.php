<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * An attachment of a module-initiated email.
 *
 * `path` lives on the `local` disk or is absolute — the implementation
 * decides how it resolves it; the fake only stores the attachment without
 * reading it.
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
