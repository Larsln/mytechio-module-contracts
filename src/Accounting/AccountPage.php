<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * One page of `Accounts::list()`: `page` starts at 1, `total` counts all
 * matches across pages, `lastPage` is at least 1.
 */
final readonly class AccountPage
{
    /**
     * @param  list<AccountInfo>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(static fn (AccountInfo $account): array => $account->toArray(), $this->items),
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'last_page' => $this->lastPage,
        ];
    }
}
