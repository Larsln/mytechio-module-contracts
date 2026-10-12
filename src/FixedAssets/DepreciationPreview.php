<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Depreciation of a fiscal year that has not been booked yet, as
 * `FixedAssets::runDepreciation()` would book it.
 */
final readonly class DepreciationPreview
{
    /**
     * @param  list<DepreciationPreviewRow>  $rows
     */
    public function __construct(
        public int $year,
        public array $rows,
        public string $total,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'rows' => array_map(static fn (DepreciationPreviewRow $row): array => $row->toArray(), $this->rows),
            'total' => $this->total,
        ];
    }
}
