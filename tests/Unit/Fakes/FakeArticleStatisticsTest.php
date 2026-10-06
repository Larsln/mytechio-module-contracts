<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleDetailStats;
use MyTechIO\Contracts\Articles\ArticleOverview;
use MyTechIO\Contracts\Fakes\FakeArticleStatistics;

it('returns an empty overview until one is seeded', function () {
    $statistics = new FakeArticleStatistics;

    $overview = $statistics->overview('2026-01-01', '2026-12-31');

    expect($overview->kpis['revenue'])->toBe('0.00')
        ->and($overview->perArticle)->toBe([]);
});

it('returns the seeded overview', function () {
    $statistics = new FakeArticleStatistics;
    $seeded = new ArticleOverview(
        kpis: ['revenue' => '100.00', 'cogs' => '50.00', 'margin' => '50.00', 'margin_percent' => '50.0', 'quantity_sold' => '1.00', 'articles_sold' => 1],
        monthly: [],
        perArticle: [],
        byKind: [],
    );
    $statistics->seedOverview($seeded);

    expect($statistics->overview('2026-01-01', '2026-12-31'))->toBe($seeded);
});

it('finds a seeded article detail by id, or null when unknown', function () {
    $statistics = new FakeArticleStatistics;
    $seeded = new ArticleDetailStats(
        kpis: ['revenue' => '10.00', 'cogs' => '5.00', 'margin' => '5.00', 'margin_percent' => '50.0', 'quantity_sold' => '1.00'],
        monthly: [],
        priceHistory: ['purchase' => [], 'sale' => []],
        stock: null,
    );
    $statistics->seedArticle(7, $seeded);

    expect($statistics->forArticle(7, '2026-01-01', '2026-12-31'))->toBe($seeded)
        ->and($statistics->forArticle(999, '2026-01-01', '2026-12-31'))->toBeNull();
});
