<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleData;
use MyTechIO\Contracts\Articles\ArticleKind;
use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\ContractException;

function articleData(string $priceB2cNet = '11.9000', string $priceB2bNet = '10.0000'): ArticleData
{
    return new ArticleData(
        id: 7,
        articleNumber: 'ART-0007',
        name: 'Webhosting Basic',
        description: 'Shared hosting',
        kind: ArticleKind::ElectronicService,
        taxClassification: TaxClassification::ElectronicService,
        unit: 'Std',
        unitName: 'Stunde',
        unitUneceCode: 'HUR',
        priceB2cNet: $priceB2cNet,
        priceB2bNet: $priceB2bNet,
        stockTracked: false,
        isActive: true,
    );
}

it('converts to an array with snake_case keys and enum values', function () {
    expect(articleData()->toArray())->toBe([
        'id' => 7,
        'article_number' => 'ART-0007',
        'name' => 'Webhosting Basic',
        'description' => 'Shared hosting',
        'kind' => 'electronic_service',
        'tax_classification' => 'electronic_service',
        'unit' => 'Std',
        'unit_name' => 'Stunde',
        'unit_unece_code' => 'HUR',
        'price_b2c_net' => '11.9000',
        'price_b2b_net' => '10.0000',
        'stock_tracked' => false,
        'is_active' => true,
    ]);
});

it('accepts a missing description and integer or negative decimal prices', function () {
    $article = new ArticleData(1, 'A-1', 'Rabatt', null, ArticleKind::Service, TaxClassification::OtherService, 'Stk', 'Stück', 'H87', '0', '-5.5', false, true);

    expect($article->description)->toBeNull()
        ->and($article->toArray()['price_b2b_net'])->toBe('-5.5');
});

it('accepts a unit without a UN/ECE code', function () {
    $article = new ArticleData(1, 'A-1', 'Pauschale', null, ArticleKind::Service, TaxClassification::OtherService, 'psch', 'Pauschale', null, '10', '10', false, true);

    expect($article->unitUneceCode)->toBeNull()
        ->and($article->toArray()['unit_unece_code'])->toBeNull();
});

it('rejects malformed prices', function (string $priceB2cNet, string $priceB2bNet) {
    articleData($priceB2cNet, $priceB2bNet);
})->with([
    'b2c comma' => ['1,50', '1.0000'],
    'b2b too many decimals' => ['1.0000', '1.00001'],
    'b2b empty' => ['1.0000', ''],
])->throws(ContractException::class);
