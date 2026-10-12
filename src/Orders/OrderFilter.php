<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Filters for `Orders::list()`; every field narrows the result, `null` leaves
 * it open. `status` is `draft`, `open`, `partial`, `completed` or `cancelled`;
 * `from`/`to` are `Y-m-d`, inclusive, applied to the order date. `search`
 * matches the order number, the project title and the contact name
 * case-insensitively. `hasOpenItems` `true` keeps the orders whose lines are
 * still to be delivered (status `open` or `partial`), `false` the others.
 */
final readonly class OrderFilter
{
    public function __construct(
        public ?string $status = null,
        public ?int $contactId = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $search = null,
        public ?bool $hasOpenItems = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'contact_id' => $this->contactId,
            'from' => $this->from,
            'to' => $this->to,
            'search' => $this->search,
            'has_open_items' => $this->hasOpenItems,
        ];
    }
}
