<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Articles\ArticleDetailStats;
use MyTechIO\Contracts\Articles\ArticleOverview;
use MyTechIO\Contracts\Articles\ArticleStatistics;

/**
 * Test-Double für `ArticleStatistics`: liefert eine leere Auswertung,
 * solange nichts gesät wurde. `seedOverview()` legt das Ergebnis von
 * `overview()` fest, `seedArticle()` das von `forArticle()` je Artikel-ID.
 */
final class FakeArticleStatistics implements ArticleStatistics
{
    private ?ArticleOverview $overview = null;

    /**
     * @var array<int, ArticleDetailStats>
     */
    private array $articles = [];

    public function overview(string $from, string $to): ArticleOverview
    {
        return $this->overview ?? new ArticleOverview(
            kpis: [
                'revenue' => '0.00',
                'cogs' => '0.00',
                'margin' => '0.00',
                'margin_percent' => null,
                'quantity_sold' => '0.00',
                'articles_sold' => 0,
            ],
            monthly: [],
            perArticle: [],
            byKind: [],
        );
    }

    public function forArticle(int $articleId, string $from, string $to): ?ArticleDetailStats
    {
        return $this->articles[$articleId] ?? null;
    }

    public function seedOverview(ArticleOverview $overview): void
    {
        $this->overview = $overview;
    }

    public function seedArticle(int $articleId, ArticleDetailStats $stats): void
    {
        $this->articles[$articleId] = $stats;
    }
}
