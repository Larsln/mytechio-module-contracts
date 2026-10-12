<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeOrders;
use MyTechIO\Contracts\Orders\NewOrder;
use MyTechIO\Contracts\Orders\NewOrderItem;
use MyTechIO\Contracts\Orders\OrderCancellation;
use MyTechIO\Contracts\Orders\OrderChanges;
use MyTechIO\Contracts\Orders\OrderFilter;
use MyTechIO\Contracts\Orders\OrderNotFoundException;
use MyTechIO\Contracts\Orders\OrderRef;
use MyTechIO\Contracts\Orders\Orders;

function orderActor(): ActorRef
{
    return new ActorRef(7, 'Admin');
}

function orderInput(array $overrides = []): NewOrder
{
    return new NewOrder(...[
        'contactId' => 8,
        'orderDate' => '2026-02-01',
        'paymentTermsDays' => 14,
        'items' => [new NewOrderItem('Beratung', '2', 'h', '50.00', 3, discountPercent: '10')],
        'projectTitle' => 'Relaunch',
        ...$overrides,
    ]);
}

it('resolves a seeded order by id and number', function () {
    $orders = new FakeOrders;
    $orders->seed(new OrderRef(4, 'AB-2026-00004', 'open', 8, '2026-02-01', null, [], '/orders/4'));

    expect($orders)->toBeInstanceOf(Orders::class)
        ->and($orders->find(4)?->number)->toBe('AB-2026-00004')
        ->and($orders->find(4)?->toArray()['url'])->toBe('/orders/4')
        ->and($orders->find(5))->toBeNull()
        ->and($orders->findByNumber('AB-2026-00004')?->id)->toBe(4)
        ->and($orders->findByNumber('AB-2026-00005'))->toBeNull();
});

it('creates a draft with computed amounts and fires OrderCreated', function () {
    $orders = new FakeOrders;
    $orders->seedContact(8, 'Muster GmbH');

    $order = $orders->create(orderInput(), orderActor());

    expect($order->status)->toBe('draft')
        ->and($order->number)->toBeNull()
        ->and($order->contactName)->toBe('Muster GmbH')
        ->and($order->items[0]->netAmount)->toBe('90.0000')
        ->and($order->items[0]->taxAmount)->toBe('17.1000')
        ->and($order->items[0]->taxRate)->toBe('19.00')
        ->and($order->items[0]->discountPercent)->toBe('10.00')
        ->and($order->netTotal)->toBe('90.0000')
        ->and($order->grossTotal)->toBe('107.1000')
        ->and($order->createdBy)->toBe(7)
        ->and($orders->findItem($order->items[0]->id)?->description)->toBe('Beratung')
        ->and($orders->events())->toBe([['event' => 'OrderCreated', 'payload' => ['order_id' => $order->id, 'number' => null, 'contact_id' => 8, 'quote_id' => null]]]);
});

it('applies the order-wide discount and the seeded tax rate', function () {
    $orders = new FakeOrders;
    $orders->seedTaxRate(3, '7.00');

    $order = $orders->create(orderInput(['customerDiscountPercent' => '10']), orderActor());

    expect($order->netSubtotal)->toBe('90.0000')
        ->and($order->netTotal)->toBe('81.0000')
        ->and($order->customerDiscountAmount)->toBe('9.0000')
        ->and($order->taxTotal)->toBe('5.6700')
        ->and($order->grandTotal)->toBe('86.6700');
});

it('refuses invalid input with a German message', function (array $overrides, string $message) {
    $orders = new FakeOrders;

    expect(fn () => $orders->create(orderInput($overrides), orderActor()))->toThrow(ContractException::class, $message);
})->with([
    'no customer' => [['contactId' => null], 'Kontakt oder eine Rechnungsadresse'],
    'no lines' => [['items' => []], 'mindestens eine Position'],
    'delivery before order' => [['requestedDeliveryDate' => '2026-01-01'], 'Wunschlieferdatum'],
    'zero quantity' => [['items' => [new NewOrderItem('x', '0', 'h', '1', 3)]], 'Menge'],
    'fixed price with discount' => [['items' => [new NewOrderItem('x', '1', 'h', '1', 3, discountPercent: '5', listPriceNet: '2')]], 'Festpreis und Rabatt %'],
    'override without reason' => [['items' => [new NewOrderItem('x', '1', 'h', '1', 3, taxScenarioOverride: 'eu_b2b_rc')]], 'Begründung'],
]);

it('updates a draft as a whole and refuses a confirmed order', function () {
    $orders = new FakeOrders;
    $order = $orders->create(orderInput(), orderActor());

    $updated = $orders->update($order->id, new OrderChanges(8, '2026-02-02', 30, [new NewOrderItem('Neu', '1', 'Stk', '10', 3)]), orderActor());

    expect($updated->items)->toHaveCount(1)
        ->and($updated->items[0]->description)->toBe('Neu')
        ->and($updated->paymentTermsDays)->toBe(30)
        ->and($updated->projectTitle)->toBeNull()
        ->and($orders->events()[1]['event'])->toBe('OrderUpdated');

    $orders->confirm($order->id, orderActor());

    expect(fn () => $orders->update($order->id, new OrderChanges(8, '2026-02-02', 30, [new NewOrderItem('Neu', '1', 'Stk', '10', 3)]), orderActor()))
        ->toThrow(ContractException::class, 'nicht mehr bearbeitet')
        ->and(fn () => $orders->update(99, new OrderChanges(8, '2026-02-02', 30, [new NewOrderItem('Neu', '1', 'Stk', '10', 3)]), orderActor()))
        ->toThrow(OrderNotFoundException::class);
});

it('confirms a draft once and draws gapless numbers per year', function () {
    $orders = new FakeOrders;
    $first = $orders->create(orderInput(), orderActor());
    $second = $orders->create(orderInput(['orderDate' => '2027-01-05']), orderActor());

    $confirmed = $orders->confirm($first->id, orderActor());

    expect($confirmed->status)->toBe('open')
        ->and($confirmed->number)->toBe('AB-2026-00001')
        ->and($orders->confirm($second->id, orderActor())->number)->toBe('AB-2027-00001')
        ->and($orders->events()[2])->toBe(['event' => 'OrderConfirmed', 'payload' => ['order_id' => $first->id, 'number' => 'AB-2026-00001', 'contact_id' => 8]])
        ->and(fn () => $orders->confirm($first->id, orderActor()))->toThrow(ContractException::class, 'nicht bestätigt');
});

it('demands the tax review confirmation for a manual scenario', function () {
    $orders = new FakeOrders;
    $order = $orders->create(orderInput(['items' => [new NewOrderItem('x', '1', 'h', '10', 3, taxScenarioOverride: 'eu_b2b_rc', taxScenarioOverrideReason: 'Kunde in Frankreich')]]), orderActor());

    expect(fn () => $orders->confirm($order->id, orderActor()))->toThrow(ContractException::class, 'Steuerbehandlung')
        ->and($orders->confirm($order->id, orderActor(), true)->status)->toBe('open');
});

it('creates an open order from a seeded quote, once', function () {
    $orders = new FakeOrders;
    $orders->seedQuote(11, orderInput());

    $order = $orders->createFromQuote(11, orderActor());

    expect($order->status)->toBe('open')
        ->and($order->number)->toBe('AB-2026-00001')
        ->and($order->quoteId)->toBe(11)
        ->and($orders->events()[0]['payload']['quote_id'])->toBe(11)
        ->and(fn () => $orders->createFromQuote(11, orderActor()))->toThrow(ContractException::class, 'Angebot')
        ->and(fn () => $orders->createFromQuote(12, orderActor()))->toThrow(ContractException::class, 'nicht gefunden');
});

it('completes only an open order and hands back the warnings', function () {
    $orders = new FakeOrders;
    $draft = $orders->create(orderInput(), orderActor());

    expect(fn () => $orders->complete($draft->id, orderActor()))->toThrow(ContractException::class, 'nicht abgeschlossen');

    $orders->confirm($draft->id, orderActor());
    $orders->returnWarnings(['Artikel A geht ins Minus.']);
    $completed = $orders->complete($draft->id, orderActor());

    expect($completed->status)->toBe('completed')
        ->and($completed->warnings)->toBe(['Artikel A geht ins Minus.'])
        ->and($orders->find($draft->id)?->warnings)->toBe([])
        ->and($orders->events()[2]['event'])->toBe('OrderCompleted');
});

it('cancels with the mode per line and records the cancellation', function () {
    $orders = new FakeOrders;
    $order = $orders->create(orderInput(['items' => [new NewOrderItem('a', '2', 'Stk', '10', 3), new NewOrderItem('b', '1', 'Stk', '5', 3)]]), orderActor());
    $orders->confirm($order->id, orderActor());
    $b = $orders->find($order->id)->items[1];

    $cancelled = $orders->cancel($order->id, new OrderCancellation('mixed', 'Material teils verbaut', [$b->id => 'consumed']), orderActor());

    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->cancellationMode)->toBe('mixed')
        ->and($cancelled->cancelledBy)->toBe(7)
        ->and($cancelled->cancelledAt)->not->toBeNull()
        ->and($cancelled->items[0]->cancellationMode)->toBe('full_return')
        ->and($cancelled->items[0]->quantityReturned)->toBe('2.0000')
        ->and($cancelled->items[1]->cancellationMode)->toBe('consumed')
        ->and($cancelled->items[1]->quantityConsumed)->toBe('1.0000')
        ->and($orders->events()[2])->toBe(['event' => 'OrderCancelled', 'payload' => ['order_id' => $order->id, 'number' => $cancelled->number, 'contact_id' => 8, 'reason' => 'Material teils verbaut', 'mode' => 'mixed']])
        ->and(fn () => $orders->cancel($order->id, new OrderCancellation('full_return', 'Doppelt storniert', []), orderActor()))->toThrow(ContractException::class, 'bereits storniert');
});

it('guards the cancellation', function () {
    $orders = new FakeOrders;
    $order = $orders->create(orderInput(), orderActor());

    expect(fn () => $orders->cancel($order->id, new OrderCancellation('full_return', 'Entwurf stornieren', []), orderActor()))->toThrow(ContractException::class, 'nicht storniert');

    $orders->confirm($order->id, orderActor());

    expect(fn () => $orders->cancel($order->id, new OrderCancellation('full_return', 'kurz', []), orderActor()))->toThrow(ContractException::class, '10 Zeichen')
        ->and(fn () => $orders->cancel($order->id, new OrderCancellation('weird', 'Grund genug hier', []), orderActor()))->toThrow(ContractException::class, 'Stornomodus')
        ->and(fn () => $orders->cancel($order->id, new OrderCancellation('mixed', 'Grund genug hier', [999 => 'consumed']), orderActor()))->toThrow(ContractException::class, 'Angaben je Position');

    $orders->linkInvoice($order->id, 31);

    expect($orders->find($order->id)?->invoiceIds)->toBe([31])
        ->and(fn () => $orders->cancel($order->id, new OrderCancellation('full_return', 'Grund genug hier', []), orderActor()))->toThrow(ContractException::class, 'aktive Rechnung');

    $orders->linkInvoice($order->id, 31, false);

    expect($orders->cancel($order->id, new OrderCancellation('consumed', 'Grund genug hier', []), orderActor())->status)->toBe('cancelled');
});

it('lists newest first, filtered, without lines', function () {
    $orders = new FakeOrders;
    $orders->seedContact(8, 'Muster GmbH');
    $orders->seedContact(9, 'Beispiel AG');
    $a = $orders->create(orderInput(['orderDate' => '2026-01-10']), orderActor());
    $b = $orders->create(orderInput(['orderDate' => '2026-03-10', 'contactId' => 9, 'projectTitle' => 'Server']), orderActor());
    $orders->confirm($b->id, orderActor());
    $c = $orders->create(orderInput(['orderDate' => '2026-02-10']), orderActor());

    $page = $orders->list();

    expect(array_map(fn (OrderRef $o): int => $o->id, $page->items))->toBe([$b->id, $c->id, $a->id])
        ->and($page->items[0]->items)->toBe([])
        ->and($page->total)->toBe(3)
        ->and($page->lastPage)->toBe(1)
        ->and($orders->find($b->id)?->items)->toHaveCount(1)
        ->and($orders->list(new OrderFilter(status: 'draft'))->total)->toBe(2)
        ->and($orders->list(new OrderFilter(contactId: 9))->total)->toBe(1)
        ->and($orders->list(new OrderFilter(from: '2026-02-01', to: '2026-02-28'))->items[0]->id)->toBe($c->id)
        ->and($orders->list(new OrderFilter(search: 'server'))->total)->toBe(1)
        ->and($orders->list(new OrderFilter(search: 'beispiel'))->total)->toBe(1)
        ->and($orders->list(new OrderFilter(hasOpenItems: true))->total)->toBe(1)
        ->and($orders->list(new OrderFilter(hasOpenItems: false))->total)->toBe(2)
        ->and($orders->list(new OrderFilter, 2, 2)->items)->toHaveCount(1)
        ->and($orders->list(new OrderFilter, 2, 2)->lastPage)->toBe(2);
});

it('records the actor of every write', function () {
    $orders = new FakeOrders;
    $order = $orders->create(orderInput(), orderActor());
    $orders->confirm($order->id, orderActor());

    expect(array_column($orders->actors(), 'action'))->toBe(['create', 'confirm'])
        ->and($orders->actors()[0]['actor']->id)->toBe(7);
});
