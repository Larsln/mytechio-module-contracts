<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\HookWarning;

/**
 * A module's hook into the order lifecycle.
 *
 * The core collects implementations via the container tag
 * `mytechio.order_hooks` (`$this->app->tag([MyHook::class], 'mytechio.order_hooks')`)
 * and calls every hook of an enabled, compatible module synchronously, INSIDE
 * the core's database transaction of the action: what a hook writes is
 * committed or rolled back together with the order, and an exception from it
 * (a `ContractException` with a German message is shown to the user) rolls the
 * whole action back. Hooks of a module that is disabled or incompatible are
 * skipped.
 */
interface OrderHook
{
    /**
     * An order was confirmed (draft to open, or created open from a quote): the
     * moment to reserve material. The order number is drawn.
     */
    public function afterConfirmed(OrderRef $order): void;

    /**
     * An order was completed (delivery/sale point in time): the moment goods
     * physically leave the stock. Remarks are returned to the user as warnings;
     * they never block completion.
     *
     * @return list<HookWarning>
     *
     * @throws ContractException to refuse the completion.
     */
    public function afterCompleted(OrderRef $order, ActorRef $actor): array;

    /**
     * An order was cancelled; `$cancellation` says per line whether the
     * material came back or was used up.
     */
    public function afterCancelled(OrderRef $order, CancellationRef $cancellation, ActorRef $actor): void;
}
