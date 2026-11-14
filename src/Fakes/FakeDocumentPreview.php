<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Tax\Decimal;
use MyTechIO\Contracts\Tax\DocumentPreview;
use MyTechIO\Contracts\Tax\DocumentPreviewData;
use MyTechIO\Contracts\Tax\DocumentPreviewException;
use MyTechIO\Contracts\Tax\PreviewedLine;
use MyTechIO\Contracts\Tax\PreviewLine;

/**
 * Test double for `DocumentPreview` that calculates like the core (four
 * decimals per step, half to even). The scenario and rate of a contact are
 * set with `taxFor()`; contacts without one are domestic at 19 %. A tax
 * category can carry its own rate (`categoryRate()`, e.g. the reduced one).
 * `failFor()` makes the preview of a contact throw; `calls()` lists what was
 * asked.
 */
final class FakeDocumentPreview implements DocumentPreview
{
    /** @var array<int, array{scenario: string, rate: string}> */
    private array $byContact = [];

    /** @var array<int, array<int, string>> contact id => tax category id => rate */
    private array $categoryRates = [];

    /** @var array<int, string> */
    private array $failures = [];

    /** @var list<string> */
    private array $warnings = [];

    /** @var list<array{contact_id: int, date: string, lines: list<PreviewLine>}> */
    private array $calls = [];

    public function taxFor(int $contactId, string $scenario, string $rate): void
    {
        $this->byContact[$contactId] = ['scenario' => $scenario, 'rate' => $rate];
    }

    public function categoryRate(int $contactId, int $taxCategoryId, string $rate): void
    {
        $this->categoryRates[$contactId][$taxCategoryId] = $rate;
    }

    public function failFor(int $contactId, string $message = 'Für diese Zeile ist kein Steuersatz hinterlegt.'): void
    {
        $this->failures[$contactId] = $message;
    }

    public function warn(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * @return list<array{contact_id: int, date: string, lines: list<PreviewLine>}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    public function preview(int $contactId, string $date, array $lines): DocumentPreviewData
    {
        $this->calls[] = ['contact_id' => $contactId, 'date' => $date, 'lines' => $lines];

        if (isset($this->failures[$contactId])) {
            throw new DocumentPreviewException($this->failures[$contactId]);
        }

        $tax = $this->byContact[$contactId] ?? ['scenario' => 'de_domestic', 'rate' => '19.00'];
        $previewed = [];
        $net = '0.0000';
        $taxTotal = '0.0000';
        $perRate = [];

        foreach ($lines as $line) {
            $rate = bcadd($this->categoryRates[$contactId][$line->taxCategoryId] ?? $tax['rate'], '0', 2);
            $amounts = Decimal::line($line->quantity, $line->unitPriceNet, $rate, $line->discountPercent);
            $previewed[] = new PreviewedLine($tax['scenario'], $rate, $amounts['net'], $amounts['tax'], $amounts['gross']);
            $net = bcadd($net, $amounts['net'], 4);
            $taxTotal = bcadd($taxTotal, $amounts['tax'], 4);
            $perRate[$rate] = bcadd($perRate[$rate] ?? '0.0000', $amounts['tax'], 4);
        }

        $gross = bcadd($net, $taxTotal, 4);

        return new DocumentPreviewData(
            lines: $previewed,
            netTotal: $net,
            taxTotal: $taxTotal,
            grossTotal: $gross,
            payable: Decimal::round($gross, 2),
            taxPerRate: $perRate,
            requiresTaxReview: $tax['scenario'] !== 'de_domestic',
            warnings: $this->warnings,
        );
    }
}
