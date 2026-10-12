<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Articles\ArticleGuard;
use MyTechIO\Contracts\Articles\ArticleRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeArticleGuard;
use MyTechIO\Contracts\Fakes\FakeInvoiceHook;
use MyTechIO\Contracts\Fakes\FakeOrderHook;
use MyTechIO\Contracts\HookWarning;
use MyTechIO\Contracts\Invoices\InvoiceHook;
use MyTechIO\Contracts\Invoices\InvoiceRef;
use MyTechIO\Contracts\Orders\CancellationRef;
use MyTechIO\Contracts\Orders\OrderHook;
use MyTechIO\Contracts\Orders\OrderItemRef;
use MyTechIO\Contracts\Orders\OrderRef;

it('records order hook calls and returns the configured warnings', function () {
    $hook = new FakeOrderHook;
    $order = new OrderRef(4, 'AB-2026-00004', 'open', 8, '2026-02-01', null, [new OrderItemRef(1, 1, 7, 'Kabel', '2.0000', 'Stk', '10.0000')]);
    $actor = new ActorRef(1, 'Tester');

    $hook->returnWarnings([new HookWarning('stock.negative', 'Bestand negativ', 7)]);
    $hook->afterConfirmed($order);
    $warnings = $hook->afterCompleted($order, $actor);
    $hook->afterCancelled($order, new CancellationRef(CancellationRef::MIXED, 'Kunde', [1 => CancellationRef::CONSUMED]), $actor);

    expect($hook)->toBeInstanceOf(OrderHook::class)
        ->and($warnings[0]->toArray())->toBe(['code' => 'stock.negative', 'message' => 'Bestand negativ', 'article_id' => 7])
        ->and($hook->confirmedCalls)->toHaveCount(1)
        ->and($hook->completedCalls[0]['actor'])->toBe($actor)
        ->and($hook->cancelledCalls[0]['cancellation']->itemModes)->toBe([1 => 'consumed'])
        ->and($order->toArray()['items'][0]['article_id'])->toBe(7);
});

it('records invoice hook calls', function () {
    $hook = new FakeInvoiceHook;
    $invoice = new InvoiceRef(3, 'RE-2026-00003', 'issued', 'invoice', 8, '2026-02-01', null, null, null, []);
    $cancellation = new InvoiceRef(4, 'RE-2026-00004', 'booked', 'cancellation', 8, '2026-02-02', null, null, 3, []);
    $actor = new ActorRef(1, 'Tester');

    expect($hook)->toBeInstanceOf(InvoiceHook::class)
        ->and($hook->afterFinalized($invoice, $actor))->toBe([]);

    $hook->afterCancelled($invoice, $cancellation, $actor);

    expect($hook->finalizedCalls)->toHaveCount(1)
        ->and($hook->cancelledCalls[0]['cancellation']->cancelsInvoiceId)->toBe(3);
});

it('vetoes article deletion unless a forced delete may override', function () {
    $guard = new FakeArticleGuard;
    $article = new ArticleRef(7, 'A-1', 'Kabel', true);

    expect($guard)->toBeInstanceOf(ArticleGuard::class);

    $guard->beforeDelete($article, false);
    $guard->vetoDeletion('Artikel hat noch Bestand.', overridable: true);

    expect(fn () => $guard->beforeDelete($article, false))->toThrow(ContractException::class, 'Bestand')
        ->and($guard->beforeDelete($article, true))->toBeNull()
        ->and($guard->deleteCalls)->toHaveCount(3);

    $guard->vetoDeletion('Gesperrt.');

    expect(fn () => $guard->beforeDelete($article, true))->toThrow(ContractException::class, 'Gesperrt');
});
