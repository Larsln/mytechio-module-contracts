<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A position of a closing statement: the accounts it sums (explicit numbers
 * and/or spans), its label and, for a balance sheet, the group it belongs to.
 */
final readonly class StatementPosition
{
    /**
     * @param  list<AccountSpan>  $spans
     * @param  list<string>  $accounts  Explicit account numbers.
     */
    public function __construct(
        public string $key,
        public string $label,
        public array $spans = [],
        public array $accounts = [],
        public ?string $group = null,
    ) {}

    public function contains(string $accountNumber): bool
    {
        if (in_array($accountNumber, $this->accounts, true)) {
            return true;
        }

        foreach ($this->spans as $span) {
            if ($span->contains($accountNumber)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'spans' => array_map(fn (AccountSpan $span): array => $span->toArray(), $this->spans),
            'accounts' => $this->accounts,
            'group' => $this->group,
        ];
    }
}
