<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * The fixed-asset schedule (Anlagespiegel) of a calendar year: one row per
 * accounting asset and the column totals (`ListOnly` assets are not part of
 * it). `totals` has the keys `cost`, `additions`, `disposals`,
 * `depreciation`, `book_start` and `book_end` (4 decimal places).
 */
final readonly class FixedAssetSchedule
{
    /**
     * @param  list<FixedAssetScheduleRow>  $rows
     * @param  array<string, string>  $totals
     */
    public function __construct(
        public int $year,
        public array $rows,
        public array $totals,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'rows' => array_map(static fn (FixedAssetScheduleRow $row): array => $row->toArray(), $this->rows),
            'totals' => $this->totals,
        ];
    }
}
