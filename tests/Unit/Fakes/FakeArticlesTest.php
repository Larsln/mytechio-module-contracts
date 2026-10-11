<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleData;
use MyTechIO\Contracts\Articles\ArticleKind;
use MyTechIO\Contracts\Articles\PriceForm;
use MyTechIO\Contracts\Articles\PriceOptionData;
use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\Fakes\FakeArticles;

function makeArticle(int $id, string $number, string $name, bool $isActive = true): ArticleData
{
    return new ArticleData($id, $number, $name, null, ArticleKind::Goods, TaxClassification::SupplyOfGoods, 'Stk', 'Stück', 'H87', '10.0000', '8.0000', true, $isActive, $id, [new PriceOptionData($id, 'Einzelpreis', PriceForm::OneOff, null, null, '10.0000', '8.0000', true)]);
}

it('finds a seeded article by id, active or inactive', function () {
    $articles = new FakeArticles;
    $articles->seed(makeArticle(1, 'A-1', 'Kabel'));
    $articles->seed(makeArticle(2, 'A-2', 'Altartikel', isActive: false));

    expect($articles->find(1)?->name)->toBe('Kabel')
        ->and($articles->find(2)?->isActive)->toBeFalse()
        ->and($articles->find(999))->toBeNull();
});

it('finds a seeded article by its exact article number', function () {
    $articles = new FakeArticles;
    $articles->seed(makeArticle(1, 'A-1', 'Kabel'));
    $articles->seed(makeArticle(2, 'A-10', 'Stecker'));

    expect($articles->findByNumber('A-10')?->id)->toBe(2)
        ->and($articles->findByNumber('a-10'))->toBeNull()
        ->and($articles->findByNumber('A-99'))->toBeNull();
});

it('searches article number and name case-insensitively in insertion order', function () {
    $articles = new FakeArticles;
    $articles->seed(makeArticle(1, 'KAB-1', 'Netzwerkkabel'));
    $articles->seed(makeArticle(2, 'STE-1', 'Kabelstecker'));
    $articles->seed(makeArticle(3, 'XYZ-1', 'Router'));

    $byName = $articles->search('KABEL');
    $byNumber = $articles->search('xyz');

    expect(array_map(fn (ArticleData $a) => $a->id, $byName))->toBe([1, 2])
        ->and(array_map(fn (ArticleData $a) => $a->id, $byNumber))->toBe([3])
        ->and($articles->search('gibt-es-nicht'))->toBe([]);
});

it('limits the search result', function () {
    $articles = new FakeArticles;
    $articles->seed(makeArticle(1, 'A-1', 'Kabel'));
    $articles->seed(makeArticle(2, 'A-2', 'Kabel'));
    $articles->seed(makeArticle(3, 'A-3', 'Kabel'));

    expect($articles->search('kabel', limit: 2))->toHaveCount(2);
});

it('skips inactive articles unless asked to include them', function () {
    $articles = new FakeArticles;
    $articles->seed(makeArticle(1, 'A-1', 'Kabel'));
    $articles->seed(makeArticle(2, 'A-2', 'Kabel alt', isActive: false));

    expect($articles->search('kabel'))->toHaveCount(1)
        ->and($articles->search('kabel', includeInactive: true))->toHaveCount(2);
});
