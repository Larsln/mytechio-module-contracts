<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleKind;
use MyTechIO\Contracts\Articles\TaxClassification;

it('is a string-backed enum with exactly three kinds', function () {
    expect(ArticleKind::Goods->value)->toBe('goods')
        ->and(ArticleKind::Service->value)->toBe('service')
        ->and(ArticleKind::ElectronicService->value)->toBe('electronic_service')
        ->and(ArticleKind::from('service'))->toBe(ArticleKind::Service)
        ->and(ArticleKind::tryFrom('recurring'))->toBeNull()
        ->and(ArticleKind::cases())->toHaveCount(3);
});

it('provides German labels', function () {
    expect(ArticleKind::Goods->label())->toBe('Ware')
        ->and(ArticleKind::Service->label())->toBe('Dienstleistung')
        ->and(ArticleKind::ElectronicService->label())->toBe('Elektronische Leistung');
});

it('pre-fills the tax classification from the kind', function () {
    expect(ArticleKind::Goods->defaultTaxClassification())->toBe(TaxClassification::SupplyOfGoods)
        ->and(ArticleKind::Service->defaultTaxClassification())->toBe(TaxClassification::OtherService)
        ->and(ArticleKind::ElectronicService->defaultTaxClassification())->toBe(TaxClassification::ElectronicService);
});
