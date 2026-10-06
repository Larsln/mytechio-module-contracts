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
 */
final readonly class CustomerAssetData
{
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
        ];
    }
}
