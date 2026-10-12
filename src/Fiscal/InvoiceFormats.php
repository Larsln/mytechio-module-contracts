<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The electronic invoice formats of the profile: which formats exist, how a
 * tax scenario maps to the format's tax category (UNCL 5305 for EN 16931), the
 * exemption reasons, the mandatory fields and the rounding. The XML
 * serialization stays in the core; the mapping is data, not code.
 */
interface InvoiceFormats
{
    /**
     * @return list<DocumentFormatInfo>
     */
    public function formats(): array;

    /**
     * Tax category of one invoice LINE: outgoing scenario, rate (decimal string, e.g. `19.00`) and
     * the code of the line's tax category (`standard`, `exempt`, ...).
     */
    public function lineTaxCategory(string $scenario, string $rate, ?string $taxCategoryCode): string;

    /**
     * Tax category of a tax BREAKDOWN group (one per category and rate); same inputs as
     * `lineTaxCategory()`. The two answers can differ where the format's rules for lines
     * and breakdown differ.
     */
    public function breakdownTaxCategory(string $scenario, string $rate, ?string $taxCategoryCode): string;

    /**
     * The exemption reason an untaxed tax category must state, or null when it needs none.
     */
    public function exemptionReason(string $taxCategory): ?ExemptionReason;

    /**
     * Fields the core must fill for a format before it writes the document (beyond the
     * format's own schema); empty when the profile requires nothing extra.
     *
     * @return list<string>
     */
    public function mandatoryFields(string $format): array;

    public function rounding(): RoundingRule;
}
