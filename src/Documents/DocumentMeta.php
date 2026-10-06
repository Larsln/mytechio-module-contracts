<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Metadaten für ein abzulegendes Dokument.
 *
 * `ownerType`/`ownerId` referenzieren das fachliche Objekt (z. B. Rechnung,
 * Kontakt), `kind` klassifiziert die Art des Dokuments (z. B. `invoice_pdf`,
 * `incoming_invoice`). `options` wird unverändert an die Implementierung
 * durchgereicht — der Kern liest hier z. B. `skip_paperless_notify`.
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
