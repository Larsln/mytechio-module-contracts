<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Gesamt-Auswertung Umsatz/Wareneinsatz/Rohertrag über alle Artikel in
 * einem Zeitraum.
 *
 * Die Array-Formen entsprechen exakt der Kern-Implementierung
 * (`ArticleAnalyticsService::overview()`).
 */
final readonly class ArticleOverview
{
    /**
     * @param  array{revenue: string, cogs: string, margin: string, margin_percent: string|null, quantity_sold: string, articles_sold: int}  $kpis
     * @param  list<array{month: string, revenue: string, cogs: string, margin: string, quantity: string}>  $monthly
     * @param  list<array<string, mixed>>  $perArticle  je Artikel u. a. id/article_number/name/kind/quantity/revenue/cogs/margin/margin_percent/revenue_share
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
