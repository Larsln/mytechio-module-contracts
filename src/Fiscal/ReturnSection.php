<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A titled group of VAT return boxes with the effect its tax amounts have on
 * the amount payable. `sortRows` orders the rows after the per-rate expansion
 * by box number (string comparison).
 */
final readonly class ReturnSection
{
    /**
     * @param  list<ReturnBox>  $boxes
     */
    public function __construct(
        public string $title,
        public ReturnTaxEffect $taxEffect,
        public array $boxes,
        public bool $sortRows = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'tax_effect' => $this->taxEffect->value,
            'boxes' => array_map(fn (ReturnBox $box): array => $box->toArray(), $this->boxes),
            'sort_rows' => $this->sortRows,
        ];
    }
}
