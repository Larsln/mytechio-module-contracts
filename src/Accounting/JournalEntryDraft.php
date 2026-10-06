<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Entwurf eines Buchungssatzes, den ein Modul im Journal verbuchen will.
 *
 * `sourceType`/`sourceId` verknüpfen die Buchung mit dem auslösenden
 * Modul-Objekt (z. B. `'domainrobot.invoice'`/`'42'`) für die
 * Beleg-Nachvollziehbarkeit; `documentReference` ist der für Menschen
 * lesbare Belegbezug.
 */
final readonly class JournalEntryDraft
{
    /**
     * @param  list<JournalLineDraft>  $lines
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $bookedOn,
        public string $description,
        public array $lines,
        public ?string $documentDate = null,
        public ?string $sourceType = null,
        public ?string $sourceId = null,
        public ?string $documentReference = null,
        public ?ActorRef $actor = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'booked_on' => $this->bookedOn,
            'description' => $this->description,
            'lines' => array_map(static fn (JournalLineDraft $line) => $line->toArray(), $this->lines),
            'document_date' => $this->documentDate,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'document_reference' => $this->documentReference,
            'actor' => $this->actor?->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
