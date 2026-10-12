<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A figure the VAT return reports next to its boxes without entering the amount
 * payable (OSS base and tax, VAT not yet due on the cash basis). `key` names it
 * in the computed result; a `tax` amount is reported at two decimal places, a
 * base in whole currency units rounded towards zero. The term is summed as
 * credit minus debit.
 */
final readonly class ReturnInfoLine
{
    public function __construct(
        public string $key,
        public string $label,
        public ReturnTerm $term,
        public bool $isTax,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['key' => $this->key, 'label' => $this->label, 'term' => $this->term->toArray(), 'is_tax' => $this->isTax];
    }
}
