<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A tax code the booking services stamp on journal lines and the VAT return
 * reads back: the code itself or, with `isPrefix`, every code starting with it
 * (rate keys are appended), what it means and its sign convention. A journal
 * code no entry of `ReturnsProfile::taxCodes()` matches is reported as
 * unmapped by the VAT return.
 */
final readonly class TaxCodeInfo
{
    public function __construct(
        public string $code,
        public string $meaning,
        public bool $debitPositive,
        public bool $isPrefix = false,
    ) {}

    public function matches(string $taxCode): bool
    {
        return $this->isPrefix ? str_starts_with($taxCode, $this->code) : $taxCode === $this->code;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'meaning' => $this->meaning,
            'debit_positive' => $this->debitPositive,
            'is_prefix' => $this->isPrefix,
        ];
    }
}
