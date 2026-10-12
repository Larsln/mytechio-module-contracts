<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * One entry of the reference mapping of a chart: the account that serves a
 * booking role, optionally narrowed to a rate key and a scenario. A null rate
 * key or scenario means "for any rate" / "for any scenario" of the role. The
 * core seeds these entries into the organization's own mapping at setup.
 */
final readonly class RoleMapping
{
    public function __construct(
        public AccountRole $role,
        public ?string $rateKey,
        public ?string $scenario,
        public string $accountNumber,
    ) {}

    /**
     * Identity of the entry inside a mapping: "role|rate|scenario", with an
     * empty segment for a null rate key or scenario.
     */
    public function key(): string
    {
        return sprintf('%s|%s|%s', $this->role->value, $this->rateKey ?? '', $this->scenario ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'rate_key' => $this->rateKey,
            'scenario' => $this->scenario,
            'account_number' => $this->accountNumber,
        ];
    }
}
