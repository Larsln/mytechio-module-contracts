<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * One line of an order. `articleId` is `null` for free-text lines; `quantity`
 * is a decimal string, `unit` the unit code and `netAmount` the line's net
 * total with 4 decimals.
 *
 * The appended fields complete the line (all optional so older callers keep
 * working): `unitPriceNet` is the effective net unit price, `discountPercent`
 * the line discount, `listPriceNet` the list-price snapshot of a fixed-price
 * line (`showListPrice`: the document prints list price and derived discount),
 * `taxScenario` the scenario the line is taxed with and `taxScenarioOverride`
 * (with `taxScenarioOverrideReason`) the manual scenario when one deviates from
 * the automatic one. `taxAmount`/`grossAmount` have 4 decimals. The delivered,
 * returned and consumed quantities are decimal strings; `cancellationMode`
 * (`full_return` or `consumed`) is set once the order was cancelled. `groupId`,
 * `groupName` and `groupCollapsed` describe the line group on the document;
 * `subscriptionId` names the subscription the line started on confirmation.
 */
final readonly class OrderItemRef
{
    public function __construct(
        public int $id,
        public int $position,
        public ?int $articleId,
        public string $description,
        public string $quantity,
        public ?string $unit,
        public string $netAmount,
        public ?string $articleNumber = null,
        public ?int $articlePriceOptionId = null,
        public ?string $unitPriceNet = null,
        public ?string $discountPercent = null,
        public ?string $listPriceNet = null,
        public bool $showListPrice = false,
        public ?int $taxCategoryId = null,
        public ?string $taxCategoryCode = null,
        public ?string $taxRate = null,
        public ?string $taxScenario = null,
        public ?string $taxScenarioOverride = null,
        public ?string $taxScenarioOverrideReason = null,
        public ?string $taxClassification = null,
        public ?string $serviceType = null,
        public ?string $taxAmount = null,
        public ?string $grossAmount = null,
        public ?int $revenueAccountId = null,
        public ?string $quantityDelivered = null,
        public ?string $quantityReturned = null,
        public ?string $quantityConsumed = null,
        public ?string $cancellationMode = null,
        public ?string $groupId = null,
        public ?string $groupName = null,
        public ?bool $groupCollapsed = null,
        public ?int $quoteItemId = null,
        public ?int $subscriptionId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'article_id' => $this->articleId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'net_amount' => $this->netAmount,
            'article_number' => $this->articleNumber,
            'article_price_option_id' => $this->articlePriceOptionId,
            'unit_price_net' => $this->unitPriceNet,
            'discount_percent' => $this->discountPercent,
            'list_price_net' => $this->listPriceNet,
            'show_list_price' => $this->showListPrice,
            'tax_category_id' => $this->taxCategoryId,
            'tax_category_code' => $this->taxCategoryCode,
            'tax_rate' => $this->taxRate,
            'tax_scenario' => $this->taxScenario,
            'tax_scenario_override' => $this->taxScenarioOverride,
            'tax_scenario_override_reason' => $this->taxScenarioOverrideReason,
            'tax_classification' => $this->taxClassification,
            'service_type' => $this->serviceType,
            'tax_amount' => $this->taxAmount,
            'gross_amount' => $this->grossAmount,
            'revenue_account_id' => $this->revenueAccountId,
            'quantity_delivered' => $this->quantityDelivered,
            'quantity_returned' => $this->quantityReturned,
            'quantity_consumed' => $this->quantityConsumed,
            'cancellation_mode' => $this->cancellationMode,
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'group_collapsed' => $this->groupCollapsed,
            'quote_item_id' => $this->quoteItemId,
            'subscription_id' => $this->subscriptionId,
        ];
    }
}
