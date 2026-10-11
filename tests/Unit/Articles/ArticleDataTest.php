<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleData;
use MyTechIO\Contracts\Articles\ArticleKind;
use MyTechIO\Contracts\Articles\PriceForm;
use MyTechIO\Contracts\Articles\PriceOptionData;
use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\ContractException;

function oneOffOption(int $id = 70, string $priceB2cNet = '11.9000', string $priceB2bNet = '10.0000'): PriceOptionData
{
    return new PriceOptionData($id, 'Einzelpreis', PriceForm::OneOff, null, null, $priceB2cNet, $priceB2bNet, true);
}

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
        defaultPriceOptionId: 70,
        priceOptions: [oneOffOption(70, $priceB2cNet, $priceB2bNet)],
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
        'default_price_option_id' => 70,
        'price_options' => [[
            'id' => 70,
            'label' => 'Einzelpreis',
            'price_form' => 'one_off',
            'interval_unit' => null,
            'interval_count' => null,
            'price_b2c_net' => '11.9000',
            'price_b2b_net' => '10.0000',
            'is_default' => true,
        ]],
    ]);
});

it('accepts a missing description and integer or negative decimal prices', function () {
    $article = new ArticleData(1, 'A-1', 'Rabatt', null, ArticleKind::Service, TaxClassification::OtherService, 'Stk', 'Stück', 'H87', '0', '-5.5', false, true, 70, [oneOffOption(70, '0', '-5.5')]);

    expect($article->description)->toBeNull()
        ->and($article->toArray()['price_b2b_net'])->toBe('-5.5');
});

it('accepts a unit without a UN/ECE code', function () {
    $article = new ArticleData(1, 'A-1', 'Pauschale', null, ArticleKind::Service, TaxClassification::OtherService, 'psch', 'Pauschale', null, '10', '10', false, true, 70, [oneOffOption(70, '10', '10')]);

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

it('exposes all price options and the default one', function () {
    $monthly = new PriceOptionData(70, 'monatlich', PriceForm::PerPeriod, 'month', 1, '13.5000', '11.0000', true);
    $yearly = new PriceOptionData(71, 'jährlich', PriceForm::PerPeriod, 'year', 1, '135.0000', '110.0000', false);

    $article = new ArticleData(1, 'A-1', 'Hosting', null, ArticleKind::ElectronicService, TaxClassification::ElectronicService, 'Stk', 'Stück', 'H87', '13.5000', '11.0000', false, true, 70, [$monthly, $yearly]);

    expect($article->priceOptions)->toHaveCount(2)
        ->and($article->defaultPriceOptionId)->toBe(70)
        ->and($article->toArray()['price_options'][1]['interval_unit'])->toBe('year');
});

it('rejects a default price option that is not listed', function () {
    new ArticleData(1, 'A-1', 'Hosting', null, ArticleKind::Goods, TaxClassification::SupplyOfGoods, 'Stk', 'Stück', 'H87', '1', '1', false, true, 99, [oneOffOption(70, '1', '1')]);
})->throws(ContractException::class);

it('rejects a price option list that holds anything but price options', function () {
    new ArticleData(1, 'A-1', 'Hosting', null, ArticleKind::Goods, TaxClassification::SupplyOfGoods, 'Stk', 'Stück', 'H87', '1', '1', false, true, 70, [oneOffOption(70, '1', '1'), 'x']);
})->throws(ContractException::class);
