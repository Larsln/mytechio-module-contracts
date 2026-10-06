<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Schreibgeschützte Sicht auf ein Kundenobjekt (z. B. eine Domain, eine
 * Lizenz) des Kerns.
 *
 * `status` ist Freitext der Implementierung (z. B. `'active'`,
 * `'cancelled'`); `findByLabel()` sortiert aktive Objekte zuerst.
 * `invoiceItemId` verweist — falls das Objekt aus einer Rechnungsposition
 * entstanden ist — auf diese Position.
 *
 * `source` ist der Modulname einer externen Quelle (z. B. `"domainrobot"`)
 * oder `null` bei einem manuell angelegten Objekt; `externalId` die
 * Kennung dort (z. B. der Domainname). `providerName` ist der Freitext-
 * Anbieter manuell angelegter Objekte (z. B. `"IONOS"`). `renewsAt`,
 * `autorenew` und `externalStatus` stammen — wenn gesetzt — vom Connector
 * der Quelle (`updateFromSource()`). `attributes` sind typspezifische
 * Felder, deren Schema das jeweilige Modul definiert.
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
