<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * The tax result of a document that does not exist yet, computed exactly
 * like an order with the same lines: per line scenario, rate and amounts,
 * the totals (four decimals, the sums of the lines), the tax per rate and the
 * amount the customer would owe (`payable`: the gross total rounded half to
 * even to two decimals, as the core does for payments).
 *
 * `requiresTaxReview` is true when the order would need a confirmed tax
 * review before it can be confirmed (any scenario other than domestic).
 * `warnings` are German sentences for staff, not for customers.
 */
final readonly class DocumentPreviewData
{
    /**
     * @param  list<PreviewedLine>  $lines  in the order of the input
     * @param  array<string, string>  $taxPerRate  rate in percent => tax amount (four decimals)
     * @param  list<string>  $warnings
     */
    public function __construct(
        public array $lines,
        public string $netTotal,
        public string $taxTotal,
        public string $grossTotal,
        public string $payable,
        public array $taxPerRate,
        public bool $requiresTaxReview,
        public array $warnings = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'lines' => array_map(static fn (PreviewedLine $line): array => $line->toArray(), $this->lines),
            'net_total' => $this->netTotal,
            'tax_total' => $this->taxTotal,
            'gross_total' => $this->grossTotal,
            'payable' => $this->payable,
            'tax_per_rate' => $this->taxPerRate,
            'requires_tax_review' => $this->requiresTaxReview,
            'warnings' => $this->warnings,
        ];
    }
}
