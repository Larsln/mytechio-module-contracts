<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * One line of `NewOrder`/`OrderChanges`. Quantities, prices and percentages
 * are decimal strings (never floats): `quantity` up to 4 decimals and not
 * zero, `unitPriceNet` up to 4 decimals, `discountPercent` up to 2.
 *
 * The tax rate and the revenue account are NOT input — the core resolves them
 * from `taxCategoryId` and the order's tax scenario. A line linked to an
 * article (`articleId`) takes the article's tax classification; a free-text
 * line for a customer outside Germany needs `taxClassification`
 * (`goods`, `service`, ...) because the rule table cannot guess it. A manual
 * `taxScenarioOverride` (a tax scenario value) needs its
 * `taxScenarioOverrideReason`.
 *
 * `listPriceNet` makes the line a fixed-price line (it is mutually exclusive
 * with `discountPercent`); `showListPrice` prints list price and derived
 * discount on the document. Lines that share a `groupId` (a UUID) form a group
 * on the document and must share their tax category; `groupName` and
 * `groupCollapsed` describe it.
 */
final readonly class NewOrderItem
{
    public function __construct(
        public string $description,
        public string $quantity,
        public string $unit,
        public string $unitPriceNet,
        public int $taxCategoryId,
        public ?int $articleId = null,
        public ?int $articlePriceOptionId = null,
        public string $discountPercent = '0',
        public ?string $listPriceNet = null,
        public bool $showListPrice = false,
        public ?string $groupId = null,
        public ?string $groupName = null,
        public ?bool $groupCollapsed = null,
        public ?string $taxClassification = null,
        public ?string $serviceType = null,
        public ?string $taxScenarioOverride = null,
        public ?string $taxScenarioOverrideReason = null,
        public ?int $revenueAccountId = null,
    ) {}

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
            'tax_category_id' => $this->taxCategoryId,
            'article_id' => $this->articleId,
            'article_price_option_id' => $this->articlePriceOptionId,
            'discount_percent' => $this->discountPercent,
            'list_price_net' => $this->listPriceNet,
            'show_list_price' => $this->showListPrice,
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'group_collapsed' => $this->groupCollapsed,
            'tax_classification' => $this->taxClassification,
            'service_type' => $this->serviceType,
            'tax_scenario_override' => $this->taxScenarioOverride,
            'tax_scenario_override_reason' => $this->taxScenarioOverrideReason,
            'revenue_account_id' => $this->revenueAccountId,
        ];
    }
}
