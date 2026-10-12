<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\DocumentFormatInfo;
use MyTechIO\Contracts\Fiscal\ExemptionReason;
use MyTechIO\Contracts\Fiscal\InvoiceFormats;
use MyTechIO\Contracts\Fiscal\RoundingRule;

/**
 * Test double for `InvoiceFormats` of `FakeFiscalProfile`: one writable format
 * (`pdf`), category `S` for a taxed line and `Z` for a zero rate, `E` with an
 * exemption reason for `third_country_export`, two decimal places on documents.
 */
final class FakeInvoiceFormats implements InvoiceFormats
{
    public function formats(): array
    {
        return [new DocumentFormatInfo('pdf', 'Fake PDF', canWrite: true, canRead: false)];
    }

    public function lineTaxCategory(string $scenario, string $rate, ?string $taxCategoryCode): string
    {
        return $this->category($scenario, $rate);
    }

    public function breakdownTaxCategory(string $scenario, string $rate, ?string $taxCategoryCode): string
    {
        return $this->category($scenario, $rate);
    }

    public function exemptionReason(string $taxCategory): ?ExemptionReason
    {
        return $taxCategory === 'E' ? new ExemptionReason('Exempt export', 'FAKE-EXEMPT') : null;
    }

    public function mandatoryFields(string $format): array
    {
        return [];
    }

    public function rounding(): RoundingRule
    {
        return new RoundingRule(4, 2);
    }

    private function category(string $scenario, string $rate): string
    {
        return match (true) {
            $scenario === 'third_country_export' => 'E',
            (float) $rate === 0.0 => 'Z',
            default => 'S',
        };
    }
}
