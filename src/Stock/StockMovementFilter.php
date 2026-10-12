<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * Filters for `Stock::movements()`; every field narrows the result, `null`
 * leaves it open. `from`/`to` are `Y-m-d` and inclusive, applied to the
 * economic date of the movement; `referenceType` uses the `StockReference`
 * type strings; `voucherNumber` selects the movements of one voucher.
 */
final readonly class StockMovementFilter
{
    public function __construct(
        public ?int $articleId = null,
        public ?StockMovementType $type = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public ?string $voucherNumber = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'type' => $this->type?->value,
            'from' => $this->from,
            'to' => $this->to,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'voucher_number' => $this->voucherNumber,
        ];
    }
}
