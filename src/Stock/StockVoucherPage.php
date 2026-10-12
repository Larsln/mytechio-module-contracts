<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * One page of `Stock::vouchers()`, newest first: `page` starts at 1, `total`
 * counts all matches across pages, `lastPage` is at least 1. The vouchers of a
 * page carry no movements (read one voucher with `Stock::findVoucher()`).
 */
final readonly class StockVoucherPage
{
    /**
     * @param  list<StockVoucherData>  $items
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
            'items' => array_map(static fn (StockVoucherData $voucher): array => $voucher->toArray(), $this->items),
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'last_page' => $this->lastPage,
        ];
    }
}
