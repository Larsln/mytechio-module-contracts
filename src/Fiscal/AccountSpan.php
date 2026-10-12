<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * An inclusive block of account numbers compared numerically, as statement
 * positions list them.
 */
final readonly class AccountSpan
{
    public function __construct(
        public string $from,
        public string $to,
    ) {}

    public function contains(string $accountNumber): bool
    {
        $number = (int) $accountNumber;

        return $number >= (int) $this->from && $number <= (int) $this->to;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['from' => $this->from, 'to' => $this->to];
    }
}
