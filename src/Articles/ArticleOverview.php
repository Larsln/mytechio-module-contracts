<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Aggregate analytics for revenue/cost of goods sold/gross margin across
 * all articles over a period.
 *
 * The array shapes correspond exactly to the core implementation
 * (`ArticleAnalyticsService::overview()`).
 */
final readonly class ArticleOverview
{
    /**
     * @param  array{revenue: string, cogs: string, margin: string, margin_percent: string|null, quantity_sold: string, articles_sold: int}  $kpis
     * @param  list<array{month: string, revenue: string, cogs: string, margin: string, quantity: string}>  $monthly
     * @param  list<array<string, mixed>>  $perArticle  per article, among others id/article_number/name/kind/quantity/revenue/cogs/margin/margin_percent/revenue_share
     * @param  list<array{kind: string, revenue: string}>  $byKind
     */
    public function __construct(
        public array $kpis,
        public array $monthly,
        public array $perArticle,
        public array $byKind,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'monthly' => $this->monthly,
            'per_article' => $this->perArticle,
            'by_kind' => $this->byKind,
        ];
    }
}
