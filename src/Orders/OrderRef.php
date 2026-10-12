<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Reference to an order as an `OrderHook` sees it. `number` is the order
 * number (`null` for a draft), `status` the core status value (`draft`,
 * `open`, `completed`, `cancelled`), `orderDate` is `Y-m-d`, `quoteId` names
 * the quote the order was created from. `items` are the lines in position
 * order.
 */
final readonly class OrderRef
{
    /**
     * @param  list<OrderItemRef>  $items
     */
    public function __construct(
        public int $id,
        public ?string $number,
        public string $status,
        public ?int $contactId,
        public ?string $orderDate = null,
        public ?int $quoteId = null,
        public array $items = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'contact_id' => $this->contactId,
            'order_date' => $this->orderDate,
            'quote_id' => $this->quoteId,
            'items' => array_map(static fn (OrderItemRef $item): array => $item->toArray(), $this->items),
        ];
    }
}
