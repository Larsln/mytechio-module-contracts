<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * One summand of a return amount: the journal lines of a tax code. `{rate}` in
 * the code stands for the rate key of the row; with `isPrefix` every code
 * starting with `code` counts; with `divideByRate` the sum is divided by the
 * rate (rate key / 100) to back-calculate a base from a tax (cash basis).
 */
final readonly class ReturnTerm
{
    public function __construct(
        public string $code,
        public bool $isPrefix = false,
        public bool $divideByRate = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['code' => $this->code, 'is_prefix' => $this->isPrefix, 'divide_by_rate' => $this->divideByRate];
    }
}
