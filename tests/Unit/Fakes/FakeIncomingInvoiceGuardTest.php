<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Documents\IncomingInvoiceGuard;
use MyTechIO\Contracts\Documents\IncomingInvoiceRef;
use MyTechIO\Contracts\Fakes\FakeIncomingInvoiceGuard;

function guardInvoiceRef(int $id = 9): IncomingInvoiceRef
{
    return new IncomingInvoiceRef($id, "ER-{$id}", 'booked', 4, "/incoming-invoices/{$id}");
}

it('implements the guard contract and approves everything by default', function () {
    $guard = new FakeIncomingInvoiceGuard;
    $guard->beforeBook(guardInvoiceRef());
    $guard->beforeCancel(guardInvoiceRef());

    expect($guard)->toBeInstanceOf(IncomingInvoiceGuard::class)
        ->and($guard->lockedItemIds(9))->toBe([]);
});

it('records every call with its arguments', function () {
    $guard = new FakeIncomingInvoiceGuard;
    $actor = new ActorRef(7, 'Admin');

    $guard->beforeBook(guardInvoiceRef(1));
    $guard->afterBooked(guardInvoiceRef(1), $actor);
    $guard->beforeCancel(guardInvoiceRef(1));
    $guard->afterCancelled(guardInvoiceRef(1), guardInvoiceRef(2), $actor);

    expect($guard->beforeBookCalls)->toHaveCount(1)
        ->and($guard->afterBookedCalls[0]['actor'])->toBe($actor)
        ->and($guard->beforeCancelCalls[0]->id)->toBe(1)
        ->and($guard->afterCancelledCalls[0]['original']->id)->toBe(1)
        ->and($guard->afterCancelledCalls[0]['cancellation']->id)->toBe(2);
});

it('vetoes booking with the configured message but still records the call', function () {
    $guard = new FakeIncomingInvoiceGuard;
    $guard->vetoBooking('Position 1: Konto falsch.');

    try {
        $guard->beforeBook(guardInvoiceRef());
    } catch (ContractException $exception) {
        expect($exception->getMessage())->toBe('Position 1: Konto falsch.')
            ->and($guard->beforeBookCalls)->toHaveCount(1);

        return;
    }

    $this->fail('Expected a ContractException.');
});

it('vetoes cancellation with the configured message', function () {
    $guard = new FakeIncomingInvoiceGuard;
    $guard->vetoCancellation('Storno nicht möglich.');

    $guard->beforeCancel(guardInvoiceRef());
})->throws(ContractException::class, 'Storno nicht möglich.');

it('returns the configured locked item ids', function () {
    $guard = new FakeIncomingInvoiceGuard;
    $guard->withLockedItemIds([3, 4]);

    expect($guard->lockedItemIds(9))->toBe([3, 4]);
});
