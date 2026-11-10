<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleMarker;
use MyTechIO\Contracts\Fakes\FakeArticleMarker;

it('marks articles with a detail and answers the list lookups', function () {
    $marker = new FakeArticleMarker('shop', 'Im Shop', 'S', 'ShoppingCart');
    $marker->mark(7, '/shop/produkt/switch');
    $marker->mark(9);

    expect($marker)->toBeInstanceOf(ArticleMarker::class)
        ->and(ArticleMarker::TAG)->toBe('mytechio.article_markers')
        ->and($marker->key())->toBe('shop')
        ->and($marker->label())->toBe('Im Shop')
        ->and($marker->symbol())->toBe('S')
        ->and($marker->icon())->toBe('ShoppingCart')
        ->and((new FakeArticleMarker)->icon())->toBeNull()
        ->and($marker->detailsFor([7, 8, 9]))->toBe([7 => '/shop/produkt/switch', 9 => null])
        ->and($marker->articleIds())->toBe([7, 9]);

    $marker->unmark(7);

    expect($marker->articleIds())->toBe([9])
        ->and($marker->detailsFor([7]))->toBe([]);
});
