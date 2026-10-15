<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Articles\ArticleData;
use MyTechIO\Contracts\Articles\Articles;

/**
 * Test double for `Articles`: in-memory store, `seed()` sets the initial
 * state. `search()` filters simply by substring (case-insensitive) over
 * `articleNumber` and `name` in insertion order — sufficient for module tests,
 * no relevance ranking like in the core.
 */
final class FakeArticles implements Articles
{
    /**
     * @var array<int, ArticleData>
     */
    private array $articles = [];

    public function seed(ArticleData $article): void
    {
        $this->articles[$article->id] = $article;
    }

    public function find(int $id): ?ArticleData
    {
        return $this->articles[$id] ?? null;
    }

    public function findMany(array $ids): array
    {
        return array_intersect_key($this->articles, array_flip($ids));
    }

    public function findByNumber(string $articleNumber): ?ArticleData
    {
        foreach ($this->articles as $article) {
            if ($article->articleNumber === $articleNumber) {
                return $article;
            }
        }

        return null;
    }

    public function search(string $query, int $limit = 20, bool $includeInactive = false): array
    {
        $needle = mb_strtolower($query);

        $matches = array_values(array_filter(
            $this->articles,
            static fn (ArticleData $article): bool => ($includeInactive || $article->isActive)
                && (str_contains(mb_strtolower($article->articleNumber), $needle)
                    || str_contains(mb_strtolower($article->name), $needle)),
        ));

        return array_slice($matches, 0, $limit);
    }

    /** All seeded articles ordered by name, then ID. */
    public function all(bool $includeInactive = false): array
    {
        $articles = array_values(array_filter(
            $this->articles,
            static fn (ArticleData $article): bool => $includeInactive || $article->isActive,
        ));

        usort($articles, static fn (ArticleData $a, ArticleData $b): int => [mb_strtolower($a->name), $a->id] <=> [mb_strtolower($b->name), $b->id]);

        return $articles;
    }
}
