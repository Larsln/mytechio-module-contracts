<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Read-only access to the core article catalog.
 *
 * Modules may look up and search articles via this contract, but not create or
 * change them — article master data remains exclusively the core's
 * responsibility.
 */
interface Articles
{
    /**
     * Returns active and inactive articles; a deleted article yields `null`.
     */
    public function find(int $id): ?ArticleData;

    public function findByNumber(string $articleNumber): ?ArticleData;

    /**
     * Matches at least article number and name (case-insensitive; the core
     * may match further fields such as the manufacturer) in an order defined
     * by the core. Inactive articles are only included with `$includeInactive`.
     *
     * @return list<ArticleData>
     */
    public function search(string $query, int $limit = 20, bool $includeInactive = false): array;
}
