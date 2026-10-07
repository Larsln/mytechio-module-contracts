<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Tax\VatIdChecker;
use MyTechIO\Contracts\Tax\VatIdCheckResult;
use MyTechIO\Contracts\Tax\VatIdCheckUnavailableException;

/**
 * Test double for `VatIdChecker`: returns a result configurable per
 * VAT ID (USt-ID), "invalid/unknown" by default. `unavailable()` switches
 * the fake to "unreachable" (throws `VatIdCheckUnavailableException` on
 * every call, like the core's null client without a module).
 */
final class FakeVatIdChecker implements VatIdChecker
{
    /**
     * @var array<string, VatIdCheckResult>
     */
    private array $results = [];

    private bool $unavailable = false;

    /**
     * @var list<array{country_code: string, vat_number: string}>
     */
    private array $calls = [];

    public function check(string $countryCode, string $vatNumber): VatIdCheckResult
    {
        $this->calls[] = ['country_code' => $countryCode, 'vat_number' => $vatNumber];

        if ($this->unavailable) {
            throw new VatIdCheckUnavailableException;
        }

        return $this->results[$countryCode.$vatNumber] ?? new VatIdCheckResult(
            valid: false,
            name: null,
            address: null,
            requestIdentifier: sprintf('FAKE-%d', count($this->calls)),
            checkedAt: '2026-01-01T00:00:00+00:00',
        );
    }

    public function seedResult(string $countryCode, string $vatNumber, VatIdCheckResult $result): void
    {
        $this->results[$countryCode.$vatNumber] = $result;
    }

    public function unavailable(bool $unavailable = true): self
    {
        $this->unavailable = $unavailable;

        return $this;
    }

    /**
     * @return list<array{country_code: string, vat_number: string}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
