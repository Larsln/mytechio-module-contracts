<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeCardPayments;
use MyTechIO\Contracts\Orders\NewOrder;
use MyTechIO\Contracts\Orders\NewOrderItem;
use MyTechIO\Contracts\Payments\CardPaymentException;
use MyTechIO\Contracts\Payments\CardPayments;
use MyTechIO\Contracts\Payments\CardPaymentStatus;
use MyTechIO\Contracts\Payments\NewCardPayment;

function newCardPayment(string $reference = 'q_1', string $amount = '248.05'): NewCardPayment
{
    return new NewCardPayment($reference, 7, $amount, 'Bestellung q_1', 'https://shop.test/ok', 'https://shop.test/abbruch');
}

it('starts a payment, finds it again and follows what the provider says', function () {
    $payments = new FakeCardPayments;
    $session = $payments->authorize(newCardPayment());

    expect($payments)->toBeInstanceOf(CardPayments::class)
        ->and($payments->available())->toBeTrue()
        ->and($session->paymentId)->toBe('pay_1')
        ->and($session->url)->toBe('https://pay.example.test/pay_1')
        ->and($payments->find('pay_1')?->toArray())->toBe(['payment_id' => 'pay_1', 'reference' => 'q_1', 'contact_id' => 7, 'status' => 'open', 'amount' => '248.05', 'currency' => 'EUR', 'order_id' => null, 'authorized_at' => null, 'capture_before' => null])
        ->and($payments->findByReference('q_1')?->paymentId)->toBe('pay_1')
        ->and($payments->findByReference('q_2'))->toBeNull()
        ->and($payments->refresh('pay_1')->status)->toBe(CardPaymentStatus::Open)
        ->and($payments->started()[0]->successUrl)->toBe('https://shop.test/ok');

    $payments->settle('pay_1', CardPaymentStatus::Authorized);

    // Nothing changes until the provider is asked.
    expect($payments->find('pay_1')?->status)->toBe(CardPaymentStatus::Open);
    $refreshed = $payments->refresh('pay_1');

    expect($refreshed->status)->toBe(CardPaymentStatus::Authorized)
        ->and($refreshed->status->isSecured())->toBeTrue()
        ->and($refreshed->authorizedAt)->not->toBeNull()
        ->and($refreshed->captureBefore)->not->toBeNull()
        ->and($payments->assignOrder('pay_1', 55)->orderId)->toBe(55)
        ->and($payments->forOrder(55)?->paymentId)->toBe('pay_1')
        ->and($payments->forOrder(56))->toBeNull();
});

it('releases a reservation but not a collected payment', function () {
    $payments = new FakeCardPayments;
    $payments->authorize(newCardPayment());
    $payments->authorize(newCardPayment('q_2'));
    $payments->settle('pay_2', CardPaymentStatus::Captured);
    $payments->refresh('pay_2');

    expect($payments->release('pay_1')->status)->toBe(CardPaymentStatus::Canceled)
        ->and($payments->released())->toBe(['pay_1'])
        ->and($payments->refresh('pay_1')->status->isDead())->toBeTrue()
        ->and(fn () => $payments->release('pay_2'))->toThrow(CardPaymentException::class, 'eingezogen');
});

it('refuses when it is off, told to fail, asked for an unknown payment or a malformed amount', function () {
    $payments = new FakeCardPayments;
    $payments->failNext('Stripe ist nicht erreichbar.');

    expect(fn () => $payments->authorize(newCardPayment()))->toThrow(CardPaymentException::class, 'nicht erreichbar')
        ->and($payments->authorize(newCardPayment())->paymentId)->toBe('pay_1')
        ->and(fn () => $payments->refresh('pay_9'))->toThrow(CardPaymentException::class)
        ->and(fn () => $payments->assignOrder('pay_9', 1))->toThrow(CardPaymentException::class)
        ->and($payments->find('pay_9'))->toBeNull()
        ->and(fn () => newCardPayment(amount: '12,50'))->toThrow(ContractException::class);

    $payments->unavailable();
    expect($payments->available())->toBeFalse()
        ->and(fn () => $payments->authorize(newCardPayment('q_3')))->toThrow(CardPaymentException::class, 'nicht eingerichtet');
});

it('carries the delivery address of an order', function () {
    $delivery = ['first_name' => 'Erika', 'last_name' => 'Muster', 'street' => 'Lagerweg', 'house_number' => '7', 'postal_code' => '50667', 'city' => 'Köln', 'country_code' => 'DE'];
    $input = new NewOrder(7, '2026-11-11', 14, [new NewOrderItem('Ware', '1', 'Stk', '10.00', 1)], deliverySnapshot: $delivery);

    expect($input->toArray()['delivery_snapshot'])->toBe($delivery)
        ->and((new NewOrder(7, '2026-11-11', 14, []))->deliverySnapshot)->toBeNull();
});
