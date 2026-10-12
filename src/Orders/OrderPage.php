<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * One page of `Orders::list()`, newest first: `page` starts at 1, `total`
 * counts all matches across pages, `lastPage` is at least 1. The orders of a
 * page carry no lines (`items` is empty; read one order with `Orders::find()`).
 */
final readonly class OrderPage
{
    /**
     * @param  list<OrderRef>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(static fn (OrderRef $order): array => $order->toArray(), $this->items),
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'last_page' => $this->lastPage,
        ];
    }
}
