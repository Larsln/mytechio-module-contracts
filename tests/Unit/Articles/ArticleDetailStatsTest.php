<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleDetailStats;

it('converts to an array and bundles purchase/sale price history', function () {
    $stats = new ArticleDetailStats(
        kpis: [
            'revenue' => '500.00',
            'cogs' => '300.00',
            'margin' => '200.00',
            'margin_percent' => '40.0',
            'quantity_sold' => '5.00',
        ],
        monthly: [
            ['month' => '2026-09', 'revenue' => '500.00', 'cogs' => '300.00', 'margin' => '200.00', 'quantity' => '5.00'],
        ],
        priceHistory: [
            'purchase' => [
                ['date' => '2026-08-01', 'price' => '50.0000', 'incidental' => '2.0000', 'total' => '52.0000'],
            ],
            'sale' => [
                ['date' => '2026-09-01', 'type' => 'list', 'price' => '100.0000'],
            ],
        ],
        stock: [
            ['date' => '2026-09-01', 'saldo' => '12.0000'],
        ],
    );

    expect($stats->toArray())->toBe([
        'kpis' => [
            'revenue' => '500.00',
            'cogs' => '300.00',
            'margin' => '200.00',
            'margin_percent' => '40.0',
            'quantity_sold' => '5.00',
        ],
        'monthly' => [
            ['month' => '2026-09', 'revenue' => '500.00', 'cogs' => '300.00', 'margin' => '200.00', 'quantity' => '5.00'],
        ],
        'price_history' => [
            'purchase' => [
                ['date' => '2026-08-01', 'price' => '50.0000', 'incidental' => '2.0000', 'total' => '52.0000'],
            ],
            'sale' => [
                ['date' => '2026-09-01', 'type' => 'list', 'price' => '100.0000'],
            ],
        ],
        'stock' => [
            ['date' => '2026-09-01', 'saldo' => '12.0000'],
        ],
    ]);
});

it('allows a null stock series for articles without stock tracking', function () {
    $stats = new ArticleDetailStats(
        kpis: ['revenue' => '0.00', 'cogs' => '0.00', 'margin' => '0.00', 'margin_percent' => null, 'quantity_sold' => '0.00'],
        monthly: [],
        priceHistory: ['purchase' => [], 'sale' => []],
        stock: null,
    );

    expect($stats->stock)->toBeNull();
});
