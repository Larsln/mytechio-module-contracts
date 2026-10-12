<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * An account from the chart of accounts.
 *
 * `type` is the account type (`asset`, `liability`, `equity`, `revenue`,
 * `expense`, `other`). `id` is the core's account ID (`null` only on data that
 * did not come from the core), `isActive` accounts accept bookings, `isCore`
 * accounts are part of the seeded chart and keep number and type forever.
 * `parentId` and `defaultTaxCategoryId` are core IDs; `taxScenario` is the
 * scenario that makes an account an automatic account (`null` otherwise).
 */
final readonly class AccountInfo
{
    public function __construct(
        public string $number,
        public string $name,
        public string $type,
        public ?int $id = null,
        public bool $isActive = true,
        public bool $isCore = false,
        public ?string $nameLong = null,
        public ?string $category = null,
        public ?int $parentId = null,
        public ?int $defaultTaxCategoryId = null,
        public ?string $taxScenario = null,
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
            'id' => $this->id,
            'is_active' => $this->isActive,
            'is_core' => $this->isCore,
            'name_long' => $this->nameLong,
            'category' => $this->category,
            'parent_id' => $this->parentId,
            'default_tax_category_id' => $this->defaultTaxCategoryId,
            'tax_scenario' => $this->taxScenario,
            'is_commonly_used' => $this->isCommonlyUsed,
        ];
    }
}
