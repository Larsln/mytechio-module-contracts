<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceItemRef;

it('converts to an array with the fixed-asset classification', function () {
    $item = new IncomingInvoiceItemRef(
        id: 31,
        incomingInvoiceId: 9,
        position: 2,
        description: 'Workstation',
        quantity: '1.000',
        netAmount: '2500.0000',
        expenseAccountNumber: '0650',
        articleId: null,
        assetKind: 'depreciable',
        assetUsefulLifeYears: 3,
    );

    expect($item->toArray())->toBe([
        'id' => 31,
        'incoming_invoice_id' => 9,
        'position' => 2,
        'description' => 'Workstation',
        'quantity' => '1.000',
        'net_amount' => '2500.0000',
        'expense_account_number' => '0650',
        'article_id' => null,
        'asset_kind' => 'depreciable',
        'asset_useful_life_years' => 3,
    ]);
});

it('defaults to an ordinary expense line without an asset classification', function () {
    $item = new IncomingInvoiceItemRef(1, 9, 1, 'Paper', null, '10.0000', null, 5);

    expect($item->assetKind)->toBeNull()
        ->and($item->assetUsefulLifeYears)->toBeNull()
        ->and($item->toArray()['quantity'])->toBeNull();
});
