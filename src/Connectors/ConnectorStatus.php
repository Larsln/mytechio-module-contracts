<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * Current status of an external asset (e.g. a domain at the
 * registrar) as reported by the connector.
 */
final readonly class ConnectorStatus
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $externalId,
        public string $status,
        public ?string $statusLabel,
        public ?string $renewsAt,
        public ?bool $autorenew,
        public ?bool $locked,
        public ?string $url,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'renews_at' => $this->renewsAt,
            'autorenew' => $this->autorenew,
            'locked' => $this->locked,
            'url' => $this->url,
            'attributes' => $this->attributes,
        ];
    }
}
