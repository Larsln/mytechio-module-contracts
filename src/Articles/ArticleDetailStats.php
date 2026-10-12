<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Detailed analytics for a single article.
 *
 * The array shapes correspond exactly to the core implementation
 * (`ArticleAnalyticsService::articleDetail()`); `priceHistory` bundles its
 * separate purchase/sale price series under the keys `purchase` and
 * `sale`. `stock` is `null` if the article is not stock-tracked or no stock
 * implementation is installed; likewise `cogs`, `margin` and `margin_percent`
 * are `null` without one.
 */
final readonly class ArticleDetailStats
{
    /**
     * @param  array{revenue: string, cogs: string|null, margin: string|null, margin_percent: string|null, quantity_sold: string}  $kpis
     * @param  list<array{month: string, revenue: string, cogs: string|null, margin: string|null, quantity: string}>  $monthly
     * @param  array{purchase: list<array{date: string, price: string, incidental: string, total: string}>, sale: list<array{date: string, type: string, price: string}>}  $priceHistory
     * @param  list<array{date: string, saldo: string}>|null  $stock
     */
    public function __construct(
        public array $kpis,
        public array $monthly,
        public array $priceHistory,
        public ?array $stock,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'monthly' => $this->monthly,
            'price_history' => $this->priceHistory,
            'stock' => $this->stock,
        ];
    }
}
