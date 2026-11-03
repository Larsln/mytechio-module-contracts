<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * Read-only view of a customer price list with its entries and the contacts
 * assigned to it. An inactive list is ignored when prices are resolved.
 */
final readonly class PriceListData
{
    /**
     * @param  list<PriceListEntryData>  $entries  ordered by id
     * @param  list<int>  $contactIds  contacts that buy on this list, ordered by id
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public bool $isActive,
        public array $entries,
        public array $contactIds,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'entries' => array_map(static fn (PriceListEntryData $entry): array => $entry->toArray(), $this->entries),
            'contact_ids' => $this->contactIds,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  shape of `toArray()`
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: $data['is_active'],
            entries: array_map(
                static fn (array $entry): PriceListEntryData => PriceListEntryData::fromArray($entry),
                $data['entries'] ?? [],
            ),
            contactIds: $data['contact_ids'] ?? [],
        );
    }
}
