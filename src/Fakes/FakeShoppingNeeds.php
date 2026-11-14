<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Purchasing\ShoppingNeed;
use MyTechIO\Contracts\Purchasing\ShoppingNeeds;

/**
 * Test double for `ShoppingNeeds`: keeps the last needs of every source.
 * `calls()` counts the replacements, `label()` returns the label a source
 * was last stated with.
 */
final class FakeShoppingNeeds implements ShoppingNeeds
{
    /** @var array<string, array<int, string>> */
    private array $needs = [];

    /** @var array<string, string> */
    private array $labels = [];

    /** @var array<string, array<int, string|null>> */
    private array $notes = [];

    private int $calls = 0;

    public function replace(string $source, string $sourceLabel, array $needs): void
    {
        $this->calls++;
        $this->labels[$source] = $sourceLabel;
        $this->needs[$source] = [];
        $this->notes[$source] = [];

        foreach ($needs as $need) {
            if (! $need instanceof ShoppingNeed) {
                continue;
            }

            $this->needs[$source][$need->articleId] = bcadd($this->needs[$source][$need->articleId] ?? '0', $need->quantity, 3);
            $this->notes[$source][$need->articleId] = $need->note;
        }
    }

    public function open(string $source): array
    {
        return $this->needs[$source] ?? [];
    }

    public function label(string $source): ?string
    {
        return $this->labels[$source] ?? null;
    }

    public function note(string $source, int $articleId): ?string
    {
        return $this->notes[$source][$articleId] ?? null;
    }

    public function calls(): int
    {
        return $this->calls;
    }
}
