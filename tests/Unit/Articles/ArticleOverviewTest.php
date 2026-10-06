<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleOverview;

it('converts to an array using the core aggregate shapes', function () {
    $overview = new ArticleOverview(
        kpis: [
            'revenue' => '1000.00',
            'cogs' => '600.00',
            'margin' => '400.00',
            'margin_percent' => '40.0',
            'quantity_sold' => '10.00',
            'articles_sold' => 3,
        ],
        monthly: [
            ['month' => '2026-09', 'revenue' => '1000.00', 'cogs' => '600.00', 'margin' => '400.00', 'quantity' => '10.00'],
        ],
        perArticle: [
            ['id' => 1, 'article_number' => 'A-0001', 'name' => 'Domain', 'kind' => 'service', 'revenue' => '1000.00'],
        ],
        byKind: [
            ['kind' => 'service', 'revenue' => '1000.00'],
        ],
    );

    expect($overview->toArray())->toBe([
        'kpis' => [
            'revenue' => '1000.00',
            'cogs' => '600.00',
            'margin' => '400.00',
            'margin_percent' => '40.0',
            'quantity_sold' => '10.00',
            'articles_sold' => 3,
        ],
        'monthly' => [
            ['month' => '2026-09', 'revenue' => '1000.00', 'cogs' => '600.00', 'margin' => '400.00', 'quantity' => '10.00'],
        ],
        'per_article' => [
            ['id' => 1, 'article_number' => 'A-0001', 'name' => 'Domain', 'kind' => 'service', 'revenue' => '1000.00'],
        ],
        'by_kind' => [
            ['kind' => 'service', 'revenue' => '1000.00'],
        ],
    ]);
});
