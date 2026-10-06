<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Detail-Auswertung für einen einzelnen Artikel.
 *
 * Die Array-Formen entsprechen exakt der Kern-Implementierung
 * (`ArticleAnalyticsService::articleDetail()`); `priceHistory` bündelt
 * deren getrennte EK-/VK-Preisreihen unter den Schlüsseln `purchase`
 * (Einkauf) und `sale` (Verkauf). `stock` ist `null`, wenn der Artikel
 * nicht bedarfsgeführt ist (`stock_tracked = false`).
 */
final readonly class ArticleDetailStats
{
    /**
     * @param  array{revenue: string, cogs: string, margin: string, margin_percent: string|null, quantity_sold: string}  $kpis
     * @param  list<array{month: string, revenue: string, cogs: string, margin: string, quantity: string}>  $monthly
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
