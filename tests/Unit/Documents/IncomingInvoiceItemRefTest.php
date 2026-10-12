<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceItemRef;

it('converts to an array with the module extras', function () {
    $item = new IncomingInvoiceItemRef(
        id: 31,
        incomingInvoiceId: 9,
        position: 2,
        description: 'Workstation',
        quantity: '1.000',
        netAmount: '2500.0000',
        expenseAccountNumber: '0650',
        articleId: null,
        extras: ['fixed-assets' => ['kind' => 'depreciable', 'useful_life_years' => 3]],
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
        'extras' => ['fixed-assets' => ['kind' => 'depreciable', 'useful_life_years' => 3]],
    ]);
});

it('defaults to an ordinary expense line without extras', function () {
    $item = new IncomingInvoiceItemRef(1, 9, 1, 'Paper', null, '10.0000', null, 5);

    expect($item->extras)->toBe([])
        ->and($item->toArray()['quantity'])->toBeNull();
});
