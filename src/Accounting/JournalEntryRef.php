<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Verweis auf einen tatsächlich gebuchten Buchungssatz.
 */
final readonly class JournalEntryRef
{
    public function __construct(
        public string $id,
        public string $entryNumber,
        public string $bookedOn,
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
        ];
    }
}
