<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * One line of an order. `articleId` is `null` for free-text lines; `quantity`
 * is a decimal string, `unit` the unit code and `netAmount` the line's net
 * total with 4 decimals.
 */
final readonly class OrderItemRef
{
    public function __construct(
        public int $id,
        public int $position,
        public ?int $articleId,
        public string $description,
        public string $quantity,
        public ?string $unit,
        public string $netAmount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'article_id' => $this->articleId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'net_amount' => $this->netAmount,
        ];
    }
}
