<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Filters for `Accounts::list()`; every field narrows the result, `null`
 * leaves it open. `active` defaults to `true` (inactive accounts are left
 * out), `null` returns both. `numberFrom`/`numberTo` bound the account number
 * inclusively as strings (SKR numbers have equal length, so a string
 * comparison is a numeric one), `search` matches number, name and long name
 * case-insensitively.
 */
final readonly class AccountFilter
{
    public function __construct(
        public ?string $type = null,
        public ?bool $active = true,
        public ?string $numberFrom = null,
        public ?string $numberTo = null,
        public ?string $search = null,
        public bool $commonlyUsedOnly = false,
        public bool $customOnly = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'active' => $this->active,
            'number_from' => $this->numberFrom,
            'number_to' => $this->numberTo,
            'search' => $this->search,
            'commonly_used_only' => $this->commonlyUsedOnly,
            'custom_only' => $this->customOnly,
        ];
    }
}
