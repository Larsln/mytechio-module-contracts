<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * What the core would store for one {@see PreviewLine}: the tax scenario
 * (`de_domestic`, `eu_b2c_oss`, `eu_b2b_igl`, ...), the rate in percent and
 * the line amounts with four decimals.
 */
final readonly class PreviewedLine
{
    public function __construct(
        public string $scenario,
        public string $taxRate,
        public string $netTotal,
        public string $taxAmount,
        public string $grossTotal,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'scenario' => $this->scenario,
            'tax_rate' => $this->taxRate,
            'net_total' => $this->netTotal,
            'tax_amount' => $this->taxAmount,
            'gross_total' => $this->grossTotal,
        ];
    }
}
