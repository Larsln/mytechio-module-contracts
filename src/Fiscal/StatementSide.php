<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * One side of a closing statement (assets, liabilities, the profit and loss
 * positions) with its positions in display order. `creditPositive` reads the
 * balances as credit minus debit (liabilities, income statement), otherwise as
 * debit minus credit.
 */
final readonly class StatementSide
{
    /**
     * @param  list<StatementPosition>  $positions
     */
    public function __construct(
        public string $key,
        public bool $creditPositive,
        public array $positions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'credit_positive' => $this->creditPositive,
            'positions' => array_map(fn (StatementPosition $position): array => $position->toArray(), $this->positions),
        ];
    }
}
