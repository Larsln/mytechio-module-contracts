<?php

declare(strict_types=1);

use MyTechIO\Contracts\Pricing\CustomerPriceData;
use MyTechIO\Contracts\Pricing\CustomerPriceSource;
use MyTechIO\Contracts\Pricing\NewPriceList;
use MyTechIO\Contracts\Pricing\NewPriceListEntry;
use MyTechIO\Contracts\Pricing\PriceListData;
use MyTechIO\Contracts\Pricing\PriceListEntryData;

it('round-trips a price list with its entries and contacts', function () {
    $list = new PriceListData(
        id: 3,
        name: 'Partner',
        description: null,
        isActive: true,
        entries: [
            new PriceListEntryData(1, 3, null, 7, null, '19.9000', null),
            new PriceListEntryData(2, 3, 4, null, null, null, '12.50'),
        ],
        contactIds: [5, 9],
    );

    expect($list->toArray())->toBe([
        'id' => 3,
        'name' => 'Partner',
        'description' => null,
        'is_active' => true,
        'entries' => [
            ['id' => 1, 'price_list_id' => 3, 'product_group_id' => null, 'article_id' => 7, 'price_option_id' => null, 'fixed_price_net' => '19.9000', 'discount_percent' => null],
            ['id' => 2, 'price_list_id' => 3, 'product_group_id' => 4, 'article_id' => null, 'price_option_id' => null, 'fixed_price_net' => null, 'discount_percent' => '12.50'],
        ],
        'contact_ids' => [5, 9],
    ])
        ->and(PriceListData::fromArray($list->toArray()))->toEqual($list);
});

it('round-trips a customer price and maps the source by value', function () {
    $price = new CustomerPriceData(5, 11, '20.0000', '18.0000', '10.00', CustomerPriceSource::ArticlePercent);

    expect($price->toArray()['source'])->toBe('article_percent')
        ->and(CustomerPriceData::fromArray($price->toArray()))->toEqual($price);
});

it('exposes the inputs as snake_case arrays with defaults', function () {
    expect((new NewPriceList('Partner'))->toArray())->toBe(['name' => 'Partner', 'description' => null, 'is_active' => true])
        ->and((new NewPriceListEntry(articleId: 7, fixedPriceNet: '9.50'))->toArray())->toBe([
            'product_group_id' => null,
            'article_id' => 7,
            'price_option_id' => null,
            'fixed_price_net' => '9.50',
            'discount_percent' => null,
        ]);
});
