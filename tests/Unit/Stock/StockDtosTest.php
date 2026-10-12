<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Stock\CogsRow;
use MyTechIO\Contracts\Stock\NewInventoryCount;
use MyTechIO\Contracts\Stock\NewStockIssue;
use MyTechIO\Contracts\Stock\NewStockReceipt;
use MyTechIO\Contracts\Stock\NewWriteOff;
use MyTechIO\Contracts\Stock\StockIssueType;
use MyTechIO\Contracts\Stock\StockLevel;
use MyTechIO\Contracts\Stock\StockMovementData;
use MyTechIO\Contracts\Stock\StockMovementFilter;
use MyTechIO\Contracts\Stock\StockMovementPage;
use MyTechIO\Contracts\Stock\StockMovementType;
use MyTechIO\Contracts\Stock\StockReference;
use MyTechIO\Contracts\Stock\StockSettings;
use MyTechIO\Contracts\Stock\StockVoucherData;
use MyTechIO\Contracts\Stock\StockVoucherFilter;
use MyTechIO\Contracts\Stock\StockVoucherPage;
use MyTechIO\Contracts\Stock\StockVoucherType;

function movementData(array $overrides = []): StockMovementData
{
    return new StockMovementData(...[
        'id' => 1,
        'articleId' => 7,
        'type' => StockMovementType::Incoming,
        'quantity' => '5.0000',
        'referenceType' => StockReference::INCOMING_INVOICE_ITEM,
        'referenceId' => 31,
        'voucherNumber' => null,
        'reason' => 'Wareneingang',
        'unitCostAtMovement' => '10.5000',
        'unitCostBase' => '10.0000',
        'unitIncidentalCost' => '0.5000',
        'bookedById' => 3,
        'occurredAt' => '2026-02-01T00:00:00+00:00',
        ...$overrides,
    ]);
}

it('describes the stock level of an article', function () {
    $level = new StockLevel(7, true, '10.0000', '4.0000', '6.0000', '8.0000', true, true);

    expect($level->toArray())->toBe([
        'article_id' => 7,
        'tracked' => true,
        'on_hand' => '10.0000',
        'reserved' => '4.0000',
        'available' => '6.0000',
        'reorder_point' => '8.0000',
        'low_stock_warning_enabled' => true,
        'is_low' => true,
    ]);
});

it('rejects malformed quantities', function (callable $build) {
    expect($build)->toThrow(ContractException::class);
})->with([
    'level' => [fn () => new StockLevel(1, true, '1,5', '0', '0', null, false, false)],
    'movement' => [fn () => movementData(['quantity' => 'abc'])],
    'cost' => [fn () => movementData(['unitCostBase' => '1.23456'])],
    'receipt' => [fn () => new NewStockReceipt(1, 'x')],
    'receipt date' => [fn () => new NewStockReceipt(1, '1', occurredOn: '2026-13-40')],
    'issue' => [fn () => new NewStockIssue(1, '1.00001')],
    'write-off' => [fn () => new NewWriteOff(1, StockMovementType::Loss, 'two', 'Schwund')],
    'count' => [fn () => new NewInventoryCount(1, '', 'Inventur')],
    'cogs' => [fn () => new CogsRow(1, '2026-02', '1', 'x')],
]);

it('serialises a movement with its snapshot', function () {
    expect(movementData()->toArray())->toBe([
        'id' => 1,
        'article_id' => 7,
        'type' => 'incoming',
        'quantity' => '5.0000',
        'reference_type' => 'incoming_invoice_item',
        'reference_id' => 31,
        'voucher_number' => null,
        'reason' => 'Wareneingang',
        'unit_cost_at_movement' => '10.5000',
        'unit_cost_base' => '10.0000',
        'unit_incidental_cost' => '0.5000',
        'booked_by_id' => 3,
        'occurred_at' => '2026-02-01T00:00:00+00:00',
    ]);
});

it('knows the signs and write-off kinds of movement types', function () {
    expect(StockMovementType::Incoming->isPositive())->toBeTrue()
        ->and(StockMovementType::Return->isPositive())->toBeTrue()
        ->and(StockMovementType::OutgoingSale->isPositive())->toBeFalse()
        ->and(StockMovementType::writeOffCases())->toBe([
            StockMovementType::Damage,
            StockMovementType::Loss,
            StockMovementType::ConsumptionNoSale,
            StockMovementType::FreeOfChargeTransfer,
        ]);
});

it('defaults an issue to a sale and serialises references', function () {
    $issue = new NewStockIssue(7, '2', reference: new StockReference(StockReference::ORDER, 4));

    expect($issue->type)->toBe(StockIssueType::Sale)
        ->and($issue->toArray()['reference'])->toBe(['type' => 'order', 'id' => 4]);
});

it('refuses an empty reference type', function () {
    expect(fn () => new StockReference('', 1))->toThrow(ContractException::class);
});

it('serialises receipts, write-offs, counts and settings', function () {
    expect((new NewStockReceipt(7, '3', '10.0000', '0.5000', '2026-02-01', 'Wareneingang', new StockReference('incoming_invoice_item', 9)))->toArray())->toBe([
        'article_id' => 7,
        'quantity' => '3',
        'unit_cost_base' => '10.0000',
        'unit_incidental_cost' => '0.5000',
        'occurred_on' => '2026-02-01',
        'reason' => 'Wareneingang',
        'reference' => ['type' => 'incoming_invoice_item', 'id' => 9],
    ])
        ->and((new NewWriteOff(7, StockMovementType::Damage, '1', 'Bruch', '2026-02-02'))->toArray())->toBe([
            'article_id' => 7,
            'type' => 'damage',
            'quantity' => '1',
            'reason' => 'Bruch',
            'voucher_date' => '2026-02-02',
        ])
        ->and((new NewInventoryCount(7, '4', 'Inventur'))->toArray()['counted_quantity'])->toBe('4')
        ->and((new StockSettings(true, '5.0000'))->toArray())->toBe([
            'low_stock_warning_enabled' => true,
            'reorder_point' => '5.0000',
            'tracked' => null,
        ]);
});

it('serialises filters and pages', function () {
    $filter = new StockMovementFilter(articleId: 7, type: StockMovementType::Loss, from: '2026-01-01');
    $page = new StockMovementPage([movementData()], 1, 1, 50, 1);

    expect($filter->toArray())->toBe([
        'article_id' => 7,
        'type' => 'loss',
        'from' => '2026-01-01',
        'to' => null,
        'reference_type' => null,
        'reference_id' => null,
        'voucher_number' => null,
    ])
        ->and((new StockVoucherFilter(type: StockVoucherType::InventoryCount))->toArray()['type'])->toBe('inventory_count')
        ->and($page->toArray()['items'][0]['id'])->toBe(1)
        ->and($page->toArray()['last_page'])->toBe(1)
        ->and((new StockVoucherPage([], 0, 1, 25, 1))->toArray()['total'])->toBe(0);
});

it('reports reversal state of a voucher', function () {
    $voucher = new StockVoucherData(1, 'LB-2026-00001', StockVoucherType::WriteOff, '2026-02-02', 'Bruch', null, '42', null, 'LB-2026-00002', 3, '2026-02-02T10:00:00+00:00', [movementData()]);

    expect($voucher->isReversed())->toBeTrue()
        ->and($voucher->isReversal())->toBeFalse()
        ->and($voucher->toArray()['movements'])->toHaveCount(1)
        ->and($voucher->toArray()['reversed_by_number'])->toBe('LB-2026-00002');
});
