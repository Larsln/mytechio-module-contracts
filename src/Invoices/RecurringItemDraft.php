<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * One item of a recurring-invoice template that a module wants to create.
 *
 * `quantity` and `unitPriceNet` are 4-decimal strings (e.g. `"1.0000"`),
 * never floats. `taxCategoryCode` and `revenueAccountNumber` are resolved by
 * the core; when `null`, the core derives them from the article or the
 * organization default. `extras` is the invoice-item extension payload keyed
 * by module name (same convention as `InvoiceItemRef`/`InvoiceItemExtension`),
 * copied onto every generated invoice item.
 */
final readonly class RecurringItemDraft
{
    /**
     * @param  array<string, mixed>  $extras
     */
    public function __construct(
        public string $description,
        public string $quantity,
        public ?string $unit,
        public string $unitPriceNet,
        public ?string $taxCategoryCode = null,
        public ?string $revenueAccountNumber = null,
        public ?int $articleId = null,
        public array $extras = [],
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::decimal($unitPriceNet, 'unitPriceNet');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price_net' => $this->unitPriceNet,
            'tax_category_code' => $this->taxCategoryCode,
            'revenue_account_number' => $this->revenueAccountNumber,
            'article_id' => $this->articleId,
            'extras' => $this->extras,
        ];
    }
}
