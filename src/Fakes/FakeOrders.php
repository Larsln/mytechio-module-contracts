<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Orders\OrderRef;
use MyTechIO\Contracts\Orders\Orders;

/**
 * Test double for `Orders`: in-memory, `seed()` sets the initial state.
 */
final class FakeOrders implements Orders
{
    /**
     * @var array<int, OrderRef>
     */
    private array $orders = [];

    public function seed(OrderRef $order): void
    {
        $this->orders[$order->id] = $order;
    }

    public function find(int $id): ?OrderRef
    {
        return $this->orders[$id] ?? null;
    }
}
