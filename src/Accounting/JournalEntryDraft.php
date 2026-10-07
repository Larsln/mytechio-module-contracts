<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Draft of a journal entry that a module wants to post to the journal.
 *
 * `sourceType`/`sourceId` link the booking to the triggering module
 * object (e.g. `'domainrobot.invoice'`/`'42'`) for document traceability;
 * `documentReference` is the human-readable document reference.
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
