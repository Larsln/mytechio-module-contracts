<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\HookWarning;
use MyTechIO\Contracts\Orders\CancellationRef;
use MyTechIO\Contracts\Orders\OrderHook;
use MyTechIO\Contracts\Orders\OrderRef;

/**
 * Test double for `OrderHook`: records every call (`confirmedCalls`,
 * `completedCalls`, `cancelledCalls`) so module tests can verify what the core
 * passed in. `returnWarnings()` sets what `afterCompleted()` hands back.
 */
final class FakeOrderHook implements OrderHook
{
    /**
     * @var list<OrderRef>
     */
    public array $confirmedCalls = [];

    /**
     * @var list<array{order: OrderRef, actor: ActorRef}>
     */
    public array $completedCalls = [];

    /**
     * @var list<array{order: OrderRef, cancellation: CancellationRef, actor: ActorRef}>
     */
    public array $cancelledCalls = [];

    /**
     * @var list<HookWarning>
     */
    private array $warnings = [];

    public function afterConfirmed(OrderRef $order): void
    {
        $this->confirmedCalls[] = $order;
    }

    public function afterCompleted(OrderRef $order, ActorRef $actor): array
    {
        $this->completedCalls[] = ['order' => $order, 'actor' => $actor];

        return $this->warnings;
    }

    public function afterCancelled(OrderRef $order, CancellationRef $cancellation, ActorRef $actor): void
    {
        $this->cancelledCalls[] = ['order' => $order, 'cancellation' => $cancellation, 'actor' => $actor];
    }

    /**
     * @param  list<HookWarning>  $warnings
     */
    public function returnWarnings(array $warnings): void
    {
        $this->warnings = $warnings;
    }
}
