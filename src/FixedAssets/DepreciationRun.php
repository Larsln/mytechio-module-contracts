<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Result of `FixedAssets::runDepreciation()`: the postings created by this
 * call (an asset that was already depreciated for the year is not in it).
 */
final readonly class DepreciationRun
{
    /**
     * @param  list<DepreciationEntryData>  $entries
     */
    public function __construct(
        public int $year,
        public int $booked,
        public string $total,
        public array $entries = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'booked' => $this->booked,
            'total' => $this->total,
            'entries' => array_map(static fn (DepreciationEntryData $entry): array => $entry->toArray(), $this->entries),
        ];
    }
}
