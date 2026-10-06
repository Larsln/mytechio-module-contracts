<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Daten zum Anlegen eines neuen Kundenobjekts.
 */
final readonly class NewCustomerAsset
{
    public function __construct(
        public int $contactId,
        public string $type,
        public string $label,
        public ?string $acquiredAt = null,
        public ?string $notes = null,
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
        ];
    }
}
