<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\AccountMap;
use MyTechIO\Contracts\Fiscal\AccountNotMappedException;
use MyTechIO\Contracts\Fiscal\AccountRange;
use MyTechIO\Contracts\Fiscal\AccountRangeKind;
use MyTechIO\Contracts\Fiscal\AccountRole;

/**
 * Test double for `AccountMap` of `FakeFiscalProfile`: fixed account numbers per
 * role. VAT roles answer one account per role for every rate and scenario unless
 * `seed()` set an account for a specific rate and scenario; `unmap()` removes a
 * role (with its seeded accounts) to test the not-mapped path.
 */
final class FakeAccountMap implements AccountMap
{
    /**
     * @var array<string, string>
     */
    private array $numbers = [
        'receivable' => '1000',
        'payable' => '1600',
        'discount' => '8700',
        'transit' => '1360',
        'retained_earnings' => '0860',
        'acquisition_cost' => '3800',
        'output_vat_due' => '1776',
        'output_vat_not_yet_due' => '1775',
        'input_vat' => '1576',
        'reverse_charge_vat' => '1787',
    ];

    /**
     * @var array<string, string>
     */
    private array $seeded = [];

    public function number(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): string
    {
        return $this->seeded[$this->key($role, $rateKey, $scenario)]
            ?? $this->numbers[$role->value]
            ?? throw new AccountNotMappedException($role, $rateKey, $scenario);
    }

    public function has(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): bool
    {
        return isset($this->seeded[$this->key($role, $rateKey, $scenario)]) || isset($this->numbers[$role->value]);
    }

    public function vatAccounts(): array
    {
        return array_values(array_unique(array_filter(
            [
                $this->numbers['output_vat_due'] ?? null,
                $this->numbers['output_vat_not_yet_due'] ?? null,
                $this->numbers['input_vat'] ?? null,
                $this->numbers['reverse_charge_vat'] ?? null,
            ],
            fn (?string $number): bool => $number !== null,
        )));
    }

    public function ranges(): array
    {
        return [
            new AccountRange(AccountRangeKind::BankLedger, '1200', '1299'),
            new AccountRange(AccountRangeKind::CashLedger, '1100', '1199'),
            new AccountRange(AccountRangeKind::LiquidAssets, '1100', '1299'),
            new AccountRange(AccountRangeKind::FixedAssets, '0100', '0999'),
        ];
    }

    public function range(AccountRangeKind $kind): AccountRange
    {
        foreach ($this->ranges() as $range) {
            if ($range->kind === $kind) {
                return $range;
            }
        }

        throw new AccountNotMappedException(AccountRole::Receivable);
    }

    public function coreAccounts(): array
    {
        return ['1000', '1600', '8000'];
    }

    /**
     * Sets the account of a role for one rate key and scenario.
     */
    public function seed(AccountRole $role, string $number, ?string $rateKey = null, ?string $scenario = null): self
    {
        $this->seeded[$this->key($role, $rateKey, $scenario)] = $number;

        return $this;
    }

    /**
     * Removes a role, including its seeded accounts, from the map.
     */
    public function unmap(AccountRole $role): self
    {
        unset($this->numbers[$role->value]);

        foreach (array_keys($this->seeded) as $key) {
            if (str_starts_with($key, $role->value.'|')) {
                unset($this->seeded[$key]);
            }
        }

        return $this;
    }

    private function key(AccountRole $role, ?string $rateKey, ?string $scenario): string
    {
        return sprintf('%s|%s|%s', $role->value, $rateKey ?? '', $scenario ?? '');
    }
}
