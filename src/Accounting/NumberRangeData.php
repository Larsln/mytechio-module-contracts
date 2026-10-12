<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * A number range as effective in the current calendar year.
 *
 * `type` is the core's range identifier (e.g. `invoice`, `company_asset`),
 * `label` its German name. `nextNumber` is the counter value of the current
 * year (the running number only, without prefix); `preview` the formatted
 * number `next()` would hand out. `isLocked` ranges (article, asset, customer
 * and supplier numbers) never change their format after the initial setup,
 * `isUsed` ranges have issued at least one number, `inCollisionGroup` ranges
 * share a document column with other ranges (a prefix is then mandatory).
 * `pending` lists the scheduled future format versions, oldest first.
 */
final readonly class NumberRangeData
{
    /**
     * @param  list<NumberRangeVersion>  $pending
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $prefix,
        public int $padding,
        public bool $includeYear,
        public int $nextNumber,
        public string $preview,
        public bool $isLocked,
        public bool $isUsed,
        public bool $inCollisionGroup,
        public array $pending = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'prefix' => $this->prefix,
            'padding' => $this->padding,
            'include_year' => $this->includeYear,
            'next_number' => $this->nextNumber,
            'preview' => $this->preview,
            'is_locked' => $this->isLocked,
            'is_used' => $this->isUsed,
            'in_collision_group' => $this->inCollisionGroup,
            'pending' => array_map(static fn (NumberRangeVersion $version): array => $version->toArray(), $this->pending),
        ];
    }
}
