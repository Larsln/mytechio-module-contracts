<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * An inclusive block of account numbers (`from` to `to`, four-digit strings
 * compared as strings of equal length) reserved for a purpose.
 */
final readonly class AccountRange
{
    public function __construct(
        public AccountRangeKind $kind,
        public string $from,
        public string $to,
    ) {}

    public function contains(string $accountNumber): bool
    {
        return strlen($accountNumber) === strlen($this->from)
            && $accountNumber >= $this->from
            && $accountNumber <= $this->to;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
