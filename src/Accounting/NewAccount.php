<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Input of `Accounts::create()`: a custom account. The core flags it custom
 * and active; it is never a core account. `number` (up to 10 characters) must
 * be unique, `type` one of `asset`, `liability`, `equity`, `revenue`,
 * `expense`, `other`; `parentId` and `defaultTaxCategoryId` are core IDs.
 */
final readonly class NewAccount
{
    public function __construct(
        public string $number,
        public string $name,
        public string $type,
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
            'name_long' => $this->nameLong,
            'category' => $this->category,
            'parent_id' => $this->parentId,
            'default_tax_category_id' => $this->defaultTaxCategoryId,
            'tax_scenario' => $this->taxScenario,
            'is_commonly_used' => $this->isCommonlyUsed,
        ];
    }
}
