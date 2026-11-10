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

    /** @var array<int, string> */
    private array $urls = [];

    public function __construct(
        private readonly string $key = 'fake',
        private readonly string $label = 'Fake-Markierung',
        private readonly string $symbol = 'F',
        private readonly ?string $icon = null,
    ) {}

    public function mark(int $contactId, ?string $detail = null, ?string $url = null): void
    {
        $this->marked[$contactId] = $detail;

        if ($url !== null) {
            $this->urls[$contactId] = $url;
        }
    }

    public function unmark(int $contactId): void
    {
        unset($this->marked[$contactId], $this->urls[$contactId]);
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

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function detailsFor(array $contactIds): array
    {
        return array_intersect_key($this->marked, array_flip($contactIds));
    }

    public function contactIds(): array
    {
        return array_keys($this->marked);
    }

    public function url(int $contactId): ?string
    {
        return array_key_exists($contactId, $this->marked) ? ($this->urls[$contactId] ?? null) : null;
    }
}
