<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * An account of the profile's chart of accounts (seed data of the user-
 * maintainable `accounts` table). `type` is one of `asset`, `liability`,
 * `equity`, `revenue`, `expense`, `other`; `isCore` accounts are protected.
 */
final readonly class ChartAccount
{
    public function __construct(
        public string $number,
        public string $name,
        public string $type,
        public ?string $nameLong = null,
        public ?string $category = null,
        public bool $isCore = false,
        public bool $isCommonlyUsed = true,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'name' => $this->name,
            'type' => $this->type,
            'name_long' => $this->nameLong,
            'category' => $this->category,
            'is_core' => $this->isCore,
            'is_commonly_used' => $this->isCommonlyUsed,
        ];
    }
}
