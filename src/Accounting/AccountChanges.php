<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Input of `Accounts::update()`: the complete set of editable fields, the
 * account is replaced by it. On a core account `number` and `type` must stay
 * unchanged (`ContractException` otherwise) and name and category are kept;
 * the long name, hierarchy, default tax category, tax scenario and the
 * commonly-used flag stay editable on every account.
 */
final readonly class AccountChanges
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
