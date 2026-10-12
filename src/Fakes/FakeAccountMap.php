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
 * role. VAT roles answer one account per role for every rate and scenario;
 * `unmap()` removes a role to test the not-mapped path.
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

    public function number(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): string
    {
        return $this->numbers[$role->value] ?? throw new AccountNotMappedException($role, $rateKey, $scenario);
    }

    public function has(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): bool
    {
        return isset($this->numbers[$role->value]);
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
     * Removes a role from the map.
     */
    public function unmap(AccountRole $role): self
    {
        unset($this->numbers[$role->value]);

        return $this;
    }
}
