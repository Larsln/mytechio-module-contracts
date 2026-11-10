<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Contacts\ContactMarker;

/**
 * In-memory marker for tests: `mark()` puts a contact under the marker with
 * an optional detail, `unmark()` removes it.
 */
final class FakeContactMarker implements ContactMarker
{
    /** @var array<int, string|null> */
    private array $marked = [];

    public function __construct(
        private readonly string $key = 'fake',
        private readonly string $label = 'Fake-Markierung',
        private readonly string $symbol = 'F',
    ) {}

    public function mark(int $contactId, ?string $detail = null): void
    {
        $this->marked[$contactId] = $detail;
    }

    public function unmark(int $contactId): void
    {
        unset($this->marked[$contactId]);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function detailsFor(array $contactIds): array
    {
        return array_intersect_key($this->marked, array_flip($contactIds));
    }

    public function contactIds(): array
    {
        return array_keys($this->marked);
    }
}
