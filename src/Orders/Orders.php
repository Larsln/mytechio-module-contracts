<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Read access to the core's orders (Aufträge) — the read side of the order
 * domain. A module that holds an order number as a reference (for example a
 * stock movement that came from an order's completion) resolves it here to the
 * number, the page URL and the lines. Creating, confirming, completing and
 * cancelling orders stays with the core UI; modules take part in those
 * transitions through `OrderHook`.
 */
interface Orders
{
    /**
     * The order with its lines, or null for an unknown ID.
     */
    public function find(int $id): ?OrderRef;
}
