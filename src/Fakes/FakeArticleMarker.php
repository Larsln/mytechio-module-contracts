<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Articles\ArticleMarker;

/**
 * In-memory marker for tests: `mark()` puts an article under the marker with
 * an optional detail, `unmark()` removes it.
 */
final class FakeArticleMarker implements ArticleMarker
{
    /** @var array<int, string|null> */
    private array $marked = [];

    public function __construct(
        private readonly string $key = 'fake',
        private readonly string $label = 'Fake-Markierung',
        private readonly string $symbol = 'F',
        private readonly ?string $icon = null,
    ) {}

    public function mark(int $articleId, ?string $detail = null): void
    {
        $this->marked[$articleId] = $detail;
    }

    public function unmark(int $articleId): void
    {
        unset($this->marked[$articleId]);
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

    public function detailsFor(array $articleIds): array
    {
        return array_intersect_key($this->marked, array_flip($articleIds));
    }

    public function articleIds(): array
    {
        return array_keys($this->marked);
    }
}
