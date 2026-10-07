<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Metadata for a document to be stored.
 *
 * `ownerType`/`ownerId` reference the domain object (e.g. invoice,
 * contact), `kind` classifies the type of document (e.g. `invoice_pdf`,
 * `incoming_invoice`). `options` is passed through to the implementation
 * unchanged — the core reads e.g. `skip_paperless_notify` from it.
 */
final readonly class DocumentMeta
{
    /**
     * @param  list<string>  $tags
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public ?string $ownerType,
        public ?int $ownerId,
        public ?string $kind,
        public ?string $title = null,
        public array $tags = [],
        public array $options = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'owner_type' => $this->ownerType,
            'owner_id' => $this->ownerId,
            'kind' => $this->kind,
            'title' => $this->title,
            'tags' => $this->tags,
            'options' => $this->options,
        ];
    }
}
