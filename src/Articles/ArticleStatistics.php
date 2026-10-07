<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Read access to the BI analytics (revenue, cost of goods sold, gross
 * margin) of the article domain — the core implements this contract via
 * its existing core tables, a module only displays the data.
 */
interface ArticleStatistics
{
    /**
     * Aggregate analytics across all articles for the period `$from`..`$to`
     * (each `Y-m-d`, inclusive).
     */
    public function overview(string $from, string $to): ArticleOverview;

    /**
     * Detailed analytics for a single article over the period, or
     * `null` if the article does not exist.
     */
    public function forArticle(int $articleId, string $from, string $to): ?ArticleDetailStats;
}
