<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Reference to a journal entry that has actually been posted.
 *
 * `sourceType`, `sourceId` and `documentReference` repeat what the booking
 * carried (see `JournalEntryDraft`), so a `JournalSourceResolver` can link the
 * entry back to the object that caused it.
 */
final readonly class JournalEntryRef
{
    public function __construct(
        public string $id,
        public string $entryNumber,
        public string $bookedOn,
        public ?string $sourceType = null,
        public ?string $sourceId = null,
        public ?string $documentReference = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entry_number' => $this->entryNumber,
            'booked_on' => $this->bookedOn,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'document_reference' => $this->documentReference,
        ];
    }
}
