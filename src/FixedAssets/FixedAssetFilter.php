<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Filters for `FixedAssets::list()`; every field narrows the result, `null`
 * leaves it open.
 *
 * Without `kind`, `ListOnly` assets are left out unless `includeListOnly` is
 * set (they are no accounting assets); an explicit `kind` always wins.
 * `year` keeps the assets that were part of the register during that calendar
 * year (acquired by its end and not disposed before its start).
 * `accountNumber` is the 0xxx account the asset is capitalized on; `search`
 * matches name, inventory number and category case-insensitively.
 */
final readonly class FixedAssetFilter
{
    public function __construct(
        public ?FixedAssetKind $kind = null,
        public bool $includeListOnly = false,
        public ?FixedAssetStatus $status = null,
        public ?int $year = null,
        public ?string $accountNumber = null,
        public ?string $search = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind?->value,
            'include_list_only' => $this->includeListOnly,
            'status' => $this->status?->value,
            'year' => $this->year,
            'account_number' => $this->accountNumber,
            'search' => $this->search,
        ];
    }
}
