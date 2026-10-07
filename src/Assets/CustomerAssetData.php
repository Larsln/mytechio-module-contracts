<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Read-only view of a customer asset (Kundenobjekt) (e.g. a domain, a
 * license) in the core.
 *
 * `status` is free text defined by the implementation (e.g. `'active'`,
 * `'cancelled'`); `findByLabel()` sorts active assets first.
 * `invoiceItemId` references the invoice item the asset originated from,
 * if any.
 *
 * `source` is the module name of an external source (e.g.
 * `"domainrobot"`), or `null` for a manually created asset; `externalId`
 * is the identifier there (e.g. the domain name). `providerName` is the
 * free-text provider of manually created assets (e.g. `"IONOS"`).
 * `renewsAt`, `autorenew`, and `externalStatus` come from the source's
 * connector (`updateFromSource()`) when set. `attributes` are
 * type-specific fields whose schema is defined by the respective module.
 */
final readonly class CustomerAssetData
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $id,
        public int $contactId,
        public string $type,
        public string $label,
        public string $status,
        public ?string $acquiredAt,
        public ?string $cancelledAt,
        public ?int $invoiceItemId,
        public ?string $notes,
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $providerName = null,
        public ?string $renewsAt = null,
        public ?bool $autorenew = null,
        public ?string $externalStatus = null,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'type' => $this->type,
            'label' => $this->label,
            'status' => $this->status,
            'acquired_at' => $this->acquiredAt,
            'cancelled_at' => $this->cancelledAt,
            'invoice_item_id' => $this->invoiceItemId,
            'notes' => $this->notes,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'provider_name' => $this->providerName,
            'renews_at' => $this->renewsAt,
            'autorenew' => $this->autorenew,
            'external_status' => $this->externalStatus,
            'attributes' => $this->attributes,
        ];
    }
}
