<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Orders\NewOrder;
use MyTechIO\Contracts\Orders\NewOrderItem;
use MyTechIO\Contracts\Orders\OrderCancellation;
use MyTechIO\Contracts\Orders\OrderChanges;
use MyTechIO\Contracts\Orders\OrderFilter;
use MyTechIO\Contracts\Orders\OrderItemRef;
use MyTechIO\Contracts\Orders\OrderNotFoundException;
use MyTechIO\Contracts\Orders\OrderPage;
use MyTechIO\Contracts\Orders\OrderRef;

it('keeps the former OrderRef and OrderItemRef constructors working', function () {
    $item = new OrderItemRef(1, 1, null, 'Beratung', '2.0000', 'h', '100.0000');
    $order = new OrderRef(4, 'AB-2026-00004', 'open', 8, '2026-02-01', null, [$item], '/orders/4');

    expect($order->items)->toBe([$item])
        ->and($order->contactName)->toBeNull()
        ->and($order->invoiceIds)->toBe([])
        ->and($order->warnings)->toBe([])
        ->and($item->showListPrice)->toBeFalse()
        ->and($item->unitPriceNet)->toBeNull();
});

it('serializes every field of an order and its lines', function () {
    $item = new OrderItemRef(
        id: 1,
        position: 1,
        articleId: 5,
        description: 'Beratung',
        quantity: '2.0000',
        unit: 'h',
        netAmount: '90.0000',
        articleNumber: 'A-1',
        unitPriceNet: '50.0000',
        discountPercent: '10.00',
        taxCategoryId: 3,
        taxCategoryCode: 'standard',
        taxRate: '19.00',
        taxScenario: 'de_domestic',
        taxAmount: '17.1000',
        grossAmount: '107.1000',
        groupId: 'g-1',
        groupName: 'Paket',
        groupCollapsed: true,
        subscriptionId: 12,
    );
    $order = new OrderRef(
        id: 4,
        number: 'AB-2026-00004',
        status: 'cancelled',
        contactId: 8,
        orderDate: '2026-02-01',
        items: [$item],
        contactName: 'Muster GmbH',
        netTotal: '90.0000',
        grandTotal: '107.1000',
        invoiceIds: [7],
        cancellationMode: 'mixed',
        cancellationReason: 'Kunde ist abgesprungen',
        cancelledBy: 2,
        cancelledAt: '2026-02-05T10:00:00+00:00',
    );

    $data = $order->toArray();

    expect($data)->toHaveKeys(['contact_name', 'requested_delivery_date', 'net_subtotal', 'grand_total', 'tax_scenario', 'billing_snapshot', 'invoice_ids', 'cancellation_mode', 'cancelled_at', 'created_by', 'warnings'])
        ->and($data['invoice_ids'])->toBe([7])
        ->and($data['items'][0])->toMatchArray(['article_number' => 'A-1', 'discount_percent' => '10.00', 'tax_category_code' => 'standard', 'group_name' => 'Paket', 'subscription_id' => 12, 'show_list_price' => false]);
});

it('serializes the write inputs, the filter and the page', function () {
    $line = new NewOrderItem('Beratung', '1', 'h', '80.00', 3, articleId: 5, groupId: 'g-1');
    $new = new NewOrder(8, '2026-02-01', 14, [$line], projectTitle: 'Projekt');
    $changes = new OrderChanges(8, '2026-02-01', 30, [$line], skontoPercent: '2', skontoDays: 7);

    expect($new->toArray()['items'][0])->toMatchArray(['description' => 'Beratung', 'tax_category_id' => 3, 'discount_percent' => '0', 'group_id' => 'g-1'])
        ->and($new->toArray())->toMatchArray(['contact_id' => 8, 'project_title' => 'Projekt', 'customer_discount_percent' => '0'])
        ->and($changes->toArray())->toMatchArray(['payment_terms_days' => 30, 'skonto_percent' => '2', 'skonto_days' => 7])
        ->and((new OrderFilter(status: 'open', hasOpenItems: true))->toArray())->toMatchArray(['status' => 'open', 'has_open_items' => true, 'search' => null])
        ->and((new OrderCancellation('mixed', 'Kunde hat widerrufen', [3 => 'consumed']))->toArray())->toBe(['mode' => 'mixed', 'reason' => 'Kunde hat widerrufen', 'item_modes' => [3 => 'consumed']]);

    $page = new OrderPage([new OrderRef(1, null, 'draft', null)], 40, 2, 25, 2);

    expect($page->toArray())->toMatchArray(['total' => 40, 'page' => 2, 'per_page' => 25, 'last_page' => 2])
        ->and($page->toArray()['items'][0]['status'])->toBe('draft');
});

it('names the unknown order in German', function () {
    $exception = new OrderNotFoundException(42);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->id)->toBe(42)
        ->and($exception->getMessage())->toBe('Auftrag mit ID 42 wurde nicht gefunden.');
});
