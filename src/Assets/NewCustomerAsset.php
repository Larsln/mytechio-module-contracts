<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Data for creating a new customer asset (Kundenobjekt).
 *
 * `source`/`externalId` set an external source directly at creation time
 * (e.g. via a connector import); `providerName` is the free-text
 * provider of manually created assets. `attributes` are type-specific
 * fields whose schema is defined by the respective module.
 */
final readonly class NewCustomerAsset
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $contactId,
        public string $type,
        public string $label,
        public ?string $acquiredAt = null,
        public ?string $notes = null,
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $providerName = null,
        public ?string $renewsAt = null,
        public ?bool $autorenew = null,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'type' => $this->type,
            'label' => $this->label,
            'acquired_at' => $this->acquiredAt,
            'notes' => $this->notes,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'provider_name' => $this->providerName,
            'renews_at' => $this->renewsAt,
            'autorenew' => $this->autorenew,
            'attributes' => $this->attributes,
        ];
    }
}
