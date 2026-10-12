<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * Filters for `Stock::vouchers()`; `from`/`to` are `Y-m-d` and inclusive,
 * applied to the voucher date; `articleId` keeps the vouchers that carry a
 * movement of that article.
 */
final readonly class StockVoucherFilter
{
    public function __construct(
        public ?StockVoucherType $type = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?int $articleId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type?->value,
            'from' => $this->from,
            'to' => $this->to,
            'article_id' => $this->articleId,
        ];
    }
}
