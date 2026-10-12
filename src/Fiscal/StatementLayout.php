<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The layout of a closing statement: its sides and positions, the position
 * collecting accounts no position names (surfaced for review, never swallowed)
 * and, for a balance sheet, the computed position that carries the period
 * result. `resultPosition` is inserted after the last position of its group.
 */
final readonly class StatementLayout
{
    public const string BALANCE_SHEET = 'balance_sheet';

    public const string PROFIT_AND_LOSS = 'profit_and_loss';

    /**
     * @param  list<StatementSide>  $sides
     */
    public function __construct(
        public string $code,
        public string $name,
        public array $sides,
        public StatementPosition $unassigned,
        public ?StatementPosition $resultPosition = null,
    ) {}

    public function side(string $key): ?StatementSide
    {
        foreach ($this->sides as $side) {
            if ($side->key === $key) {
                return $side;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'sides' => array_map(fn (StatementSide $side): array => $side->toArray(), $this->sides),
            'unassigned' => $this->unassigned->toArray(),
            'result_position' => $this->resultPosition?->toArray(),
        ];
    }
}
