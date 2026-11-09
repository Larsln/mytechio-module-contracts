<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Tax\TaxCategories;
use MyTechIO\Contracts\Tax\TaxCategoryData;

/**
 * Test double for `TaxCategories`: in-memory store, `seed()` adds categories in
 * the order `all()` returns them; seeding an id again replaces that category.
 */
final class FakeTaxCategories implements TaxCategories
{
    /**
     * @var array<int, TaxCategoryData>
     */
    private array $categories = [];

    public function seed(TaxCategoryData $category): void
    {
        $this->categories[$category->id] = $category;
    }

    public function all(bool $includeInactive = false): array
    {
        return array_values(array_filter(
            $this->categories,
            static fn (TaxCategoryData $category): bool => $includeInactive || $category->isActive,
        ));
    }

    public function find(int $id): ?TaxCategoryData
    {
        return $this->categories[$id] ?? null;
    }
}
