<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\InvoiceDraftItem;

it('defaults discountPercent to 0 and customerAssetIds to an empty array', function () {
    $item = new InvoiceDraftItem(description: 'Domain example.de', quantity: '1.0000', unitPriceNet: '19.9900');

    expect($item->discountPercent)->toBe('0')
        ->and($item->customerAssetIds)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $item = new InvoiceDraftItem(
        description: 'Domain example.de',
        quantity: '1.0000',
        unitPriceNet: '19.9900',
        unit: 'Stk',
        articleId: 3,
        taxCategoryId: 1,
        discountPercent: '10.0000',
        customerAssetIds: [7],
    );

    expect($item->toArray())->toBe([
        'description' => 'Domain example.de',
        'quantity' => '1.0000',
        'unit_price_net' => '19.9900',
        'unit' => 'Stk',
        'article_id' => 3,
        'tax_category_id' => 1,
        'discount_percent' => '10.0000',
        'customer_asset_ids' => [7],
    ]);
});
