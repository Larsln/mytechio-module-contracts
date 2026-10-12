<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * How amounts are rounded: the scale the calculation runs at, the scale a
 * document shows and the rounding mode (`HALF_EVEN`, banker's rounding).
 */
final readonly class RoundingRule
{
    public const string HALF_EVEN = 'half_even';

    public function __construct(
        public int $calculationScale,
        public int $displayScale,
        public string $mode = self::HALF_EVEN,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['calculation_scale' => $this->calculationScale, 'display_scale' => $this->displayScale, 'mode' => $this->mode];
    }
}
