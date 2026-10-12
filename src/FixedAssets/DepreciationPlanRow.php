<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * One year of an asset's depreciation plan: opening book value, depreciation
 * and closing book value (4 decimal places).
 */
final readonly class DepreciationPlanRow
{
    public function __construct(
        public int $year,
        public string $opening,
        public string $depreciation,
        public string $closing,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'opening' => $this->opening,
            'depreciation' => $this->depreciation,
            'closing' => $this->closing,
        ];
    }
}
