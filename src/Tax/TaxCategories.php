<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * Read-only view of the organisation's tax categories in the core — modules
 * pick and store category ids, they never create or change categories.
 */
interface TaxCategories
{
    /**
     * Returns the categories in the order the core lists them; inactive ones
     * only when `$includeInactive` is set.
     *
     * @return list<TaxCategoryData>
     */
    public function all(bool $includeInactive = false): array;

    public function find(int $id): ?TaxCategoryData;
}
