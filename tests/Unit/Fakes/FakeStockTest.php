<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeStock;
use MyTechIO\Contracts\Stock\NewInventoryCount;
use MyTechIO\Contracts\Stock\NewStockIssue;
use MyTechIO\Contracts\Stock\NewStockReceipt;
use MyTechIO\Contracts\Stock\NewWriteOff;
use MyTechIO\Contracts\Stock\Stock;
use MyTechIO\Contracts\Stock\StockArticleNotFoundException;
use MyTechIO\Contracts\Stock\StockIssueType;
use MyTechIO\Contracts\Stock\StockMovementFilter;
use MyTechIO\Contracts\Stock\StockMovementType;
use MyTechIO\Contracts\Stock\StockReference;
use MyTechIO\Contracts\Stock\StockSettings;
use MyTechIO\Contracts\Stock\StockVoucherFilter;
use MyTechIO\Contracts\Stock\StockVoucherNotFoundException;
use MyTechIO\Contracts\Stock\StockVoucherType;

function stockActor(): ActorRef
{
    return new ActorRef(1, 'Tester');
}

function fakeStock(): FakeStock
{
    $stock = new FakeStock;
    $stock->seedArticle(1, onHand: '10');
    $stock->seedArticle(2, tracked: false);

    return $stock;
}

it('implements the Stock contract', function () {
    expect(new FakeStock)->toBeInstanceOf(Stock::class);
});

it('reports the level and leaves unknown articles out of levels()', function () {
    $stock = fakeStock();

    expect($stock->level(1)->onHand)->toBe('10.0000')
        ->and($stock->level(2)->tracked)->toBeFalse()
        ->and(array_keys($stock->levels([1, 2, 99])))->toBe([1, 2])
        ->and(fn () => $stock->level(99))->toThrow(StockArticleNotFoundException::class);
});

it('books receipts and issues and keeps the cost snapshot', function () {
    $stock = fakeStock();

    $receipt = $stock->recordReceipt(new NewStockReceipt(1, '5', '10.0000', '0.5000', '2026-02-01', reference: new StockReference('incoming_invoice_item', 9)), stockActor());
    $sale = $stock->recordIssue(new NewStockIssue(1, '3', unitCost: '10.0000', reference: new StockReference('order', 4)), stockActor());
    $return = $stock->recordIssue(new NewStockIssue(1, '1', StockIssueType::Return), stockActor());

    expect($receipt->type)->toBe(StockMovementType::Incoming)
        ->and($receipt->unitCostAtMovement)->toBe('10.5000')
        ->and($sale->quantity)->toBe('-3')
        ->and($return->type)->toBe(StockMovementType::Return)
        ->and($stock->level(1)->onHand)->toBe('13.0000')
        ->and($stock->receipts(1))->toHaveCount(1)
        ->and($stock->receipts(1, new DateTimeImmutable('2026-03-01')))->toBe([]);
});

it('lets a sale take the stock negative but refuses a write-off to', function () {
    $stock = fakeStock();

    $stock->recordIssue(new NewStockIssue(1, '12'), stockActor());

    expect($stock->level(1)->onHand)->toBe('-2.0000')
        ->and(fn () => $stock->writeOff(new NewWriteOff(1, StockMovementType::Loss, '1', 'Schwund'), stockActor()))
        ->toThrow(ContractException::class, 'Ausbuchen nicht möglich');
});

it('refuses movements on untracked articles and non-positive quantities', function () {
    $stock = fakeStock();

    expect(fn () => $stock->recordReceipt(new NewStockReceipt(2, '1'), stockActor()))->toThrow(ContractException::class)
        ->and(fn () => $stock->recordReceipt(new NewStockReceipt(1, '0'), stockActor()))->toThrow(ContractException::class)
        ->and(fn () => $stock->reserve(2, '1', new StockReference('order', 1)))->toThrow(ContractException::class);
});

it('reserves and releases without going below zero', function () {
    $stock = fakeStock();
    $reference = new StockReference('order', 4);

    expect($stock->reserve(1, '4', $reference)->available)->toBe('6.0000')
        ->and($stock->release(1, '9', $reference)->reserved)->toBe('0.0000');
});

it('writes off on a voucher with a gapless number and a reason', function () {
    $stock = fakeStock();

    $voucher = $stock->writeOff(new NewWriteOff(1, StockMovementType::Damage, '2', 'Bruch', '2026-02-02'), stockActor());

    expect($voucher->number)->toBe('LB-2026-00001')
        ->and($voucher->type)->toBe(StockVoucherType::WriteOff)
        ->and($voucher->movements)->toHaveCount(1)
        ->and($voucher->movements[0]->quantity)->toBe('-2')
        ->and($stock->level(1)->onHand)->toBe('8.0000')
        ->and(fn () => $stock->writeOff(new NewWriteOff(1, StockMovementType::Incoming, '1', 'x'), stockActor()))->toThrow(ContractException::class)
        ->and(fn () => $stock->writeOff(new NewWriteOff(1, StockMovementType::Loss, '1', ' '), stockActor()))->toThrow(ContractException::class);
});

it('sets the counted stock and books nothing when it matches', function () {
    $stock = fakeStock();

    expect($stock->inventoryCount(new NewInventoryCount(1, '10', 'Inventur'), stockActor()))->toBeNull();

    $voucher = $stock->inventoryCount(new NewInventoryCount(1, '7', 'Inventur'), stockActor());

    expect($voucher?->type)->toBe(StockVoucherType::InventoryCount)
        ->and($voucher?->movements[0]->quantity)->toBe('-3.0000')
        ->and($stock->level(1)->onHand)->toBe('7.0000');
});

it('reverses a voucher once through a counter-voucher', function () {
    $stock = fakeStock();
    $voucher = $stock->writeOff(new NewWriteOff(1, StockMovementType::Loss, '2', 'Schwund'), stockActor());

    $counter = $stock->reverseVoucher($voucher->number, stockActor(), 'Irrtum');

    expect($counter->reversesNumber)->toBe($voucher->number)
        ->and($stock->findVoucher($voucher->number)?->reversedByNumber)->toBe($counter->number)
        ->and($stock->level(1)->onHand)->toBe('10.0000')
        ->and(fn () => $stock->reverseVoucher($voucher->number, stockActor(), 'nochmal'))->toThrow(ContractException::class, 'bereits storniert')
        ->and(fn () => $stock->reverseVoucher($counter->number, stockActor(), 'Storno vom Storno'))->toThrow(ContractException::class)
        ->and(fn () => $stock->reverseVoucher('LB-2026-99999', stockActor(), 'x'))->toThrow(StockVoucherNotFoundException::class);
});

it('filters and paginates movements and vouchers', function () {
    $stock = fakeStock();
    $stock->recordReceipt(new NewStockReceipt(1, '1', '1.0000', occurredOn: '2026-02-01', reference: new StockReference('order', 1)), stockActor());
    $stock->writeOff(new NewWriteOff(1, StockMovementType::Loss, '1', 'Schwund', '2026-02-03'), stockActor());
    $stock->inventoryCount(new NewInventoryCount(1, '0', 'Inventur', '2026-02-04'), stockActor());

    expect($stock->movements(new StockMovementFilter(type: StockMovementType::Loss))->total)->toBe(1)
        ->and($stock->movements(new StockMovementFilter(referenceType: 'order', referenceId: 1))->total)->toBe(1)
        ->and($stock->movements(new StockMovementFilter(from: '2026-02-03'))->total)->toBe(2)
        ->and($stock->movements(perPage: 2)->lastPage)->toBe(2)
        ->and($stock->vouchers(new StockVoucherFilter(type: StockVoucherType::WriteOff))->total)->toBe(1)
        ->and($stock->vouchers()->items[0]->number)->toBe('LB-2026-00002')
        ->and($stock->vouchers()->items[0]->movements)->toBe([]);
});

it('updates settings and lists low-stock articles', function () {
    $stock = fakeStock();

    $level = $stock->updateSettings(1, new StockSettings(true, '10'), stockActor());

    expect($level->isLow)->toBeTrue()
        ->and($stock->lowStock())->toHaveCount(1)
        ->and(fn () => $stock->updateSettings(1, new StockSettings(true), stockActor()))->toThrow(ContractException::class)
        ->and($stock->updateSettings(1, new StockSettings(false, '10', tracked: false), stockActor())->tracked)->toBeFalse();
});

it('reports the seeded cost of goods sold inside the period', function () {
    $stock = fakeStock();
    $stock->seedCostOfGoodsSold(1, '2026-02', '3', '30');
    $stock->seedCostOfGoodsSold(1, '2026-05', '1', '10');

    $rows = $stock->costOfGoodsSold(new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-03-31'));

    expect($rows)->toHaveCount(1)
        ->and($rows[0]->toArray())->toBe(['article_id' => 1, 'month' => '2026-02', 'quantity' => '3.0000', 'cogs' => '30.0000']);
});

it('records the domain events the implementation would fire', function () {
    $stock = fakeStock();
    $stock->updateSettings(1, new StockSettings(true, '9'), stockActor());
    $stock->writeOff(new NewWriteOff(1, StockMovementType::Loss, '2', 'Schwund'), stockActor());

    expect(array_column($stock->events(), 'event'))->toBe([
        'StockSettingsChanged',
        'StockMovementRecorded',
        'LowStockDetected',
        'StockVoucherCreated',
    ]);
});
