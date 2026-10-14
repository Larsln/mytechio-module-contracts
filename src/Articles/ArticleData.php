<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;

/**
 * Read-only view of a catalog article in the core.
 *
 * `priceB2cNet` and `priceB2bNet` are the net prices of the article's default
 * price option as decimal strings with up to 4 decimals, never floats.
 * `priceOptions` lists all price options of the article (at least one, ordered
 * as maintained in the core) and `defaultPriceOptionId` names the default one
 * among them. `unit` is the core unit code printed on document
 * lines (e.g. `"Stk"`, `"Std"`), `unitName` its display name (e.g. `"Stück"`)
 * and `unitUneceCode` the UN/ECE Recommendation 20 code (e.g. `"H87"`,
 * `"HUR"`), or `null` when the unit has none. `stockTracked` tells whether the
 * stock of the article is tracked: the value comes from the stock
 * implementation (`Stock::level()`), so it is `false` while no module provides
 * stock keeping — the core itself no longer stores such a flag.
 * `lastPurchasePriceNet` is the article's last known net purchase price per
 * unit (decimal string, `null` before the first purchase) and `taxRate` the
 * German VAT rate of its tax category in percent without a percent sign
 * (`"19"`, `"7"`, `"0"`; `null` when the category has none) — what a module
 * needs to value and book goods that leave the stock.
 * `manufacturer` is the manufacturer's name, `manufacturerLabel` the
 * manufacturer's own designation of the article ("Bezeichnung des Herstellers"),
 * `productGroupId` and `productGroupName` the product group the article is filed
 * under; each is `null` when not maintained.
 */
final readonly class ArticleData
{
    /**
     * @param  list<PriceOptionData>  $priceOptions
     */
    public function __construct(
        public int $id,
        public string $articleNumber,
        public string $name,
        public ?string $description,
        public ArticleKind $kind,
        public TaxClassification $taxClassification,
        public string $unit,
        public string $unitName,
        public ?string $unitUneceCode,
        public string $priceB2cNet,
        public string $priceB2bNet,
        public bool $stockTracked,
        public bool $isActive,
        public int $defaultPriceOptionId,
        public array $priceOptions,
        public ?string $lastPurchasePriceNet = null,
        public ?string $taxRate = null,
        public ?string $manufacturer = null,
        public ?string $manufacturerLabel = null,
        public ?int $productGroupId = null,
        public ?string $productGroupName = null,
    ) {
        Validate::decimal($priceB2cNet, 'priceB2cNet');
        Validate::decimal($priceB2bNet, 'priceB2bNet');

        if ($lastPurchasePriceNet !== null) {
            Validate::decimal($lastPurchasePriceNet, 'lastPurchasePriceNet');
        }

        if ($taxRate !== null) {
            Validate::decimal($taxRate, 'taxRate');
        }

        $defaultIsListed = false;

        foreach ($priceOptions as $option) {
            if (! $option instanceof PriceOptionData) {
                throw new ContractException('priceOptions must contain PriceOptionData only.');
            }

            $defaultIsListed = $defaultIsListed || $option->id === $defaultPriceOptionId;
        }

        if (! $defaultIsListed) {
            throw new ContractException('defaultPriceOptionId must be one of the priceOptions.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'article_number' => $this->articleNumber,
            'name' => $this->name,
            'description' => $this->description,
            'kind' => $this->kind->value,
            'tax_classification' => $this->taxClassification->value,
            'unit' => $this->unit,
            'unit_name' => $this->unitName,
            'unit_unece_code' => $this->unitUneceCode,
            'price_b2c_net' => $this->priceB2cNet,
            'price_b2b_net' => $this->priceB2bNet,
            'stock_tracked' => $this->stockTracked,
            'is_active' => $this->isActive,
            'default_price_option_id' => $this->defaultPriceOptionId,
            'price_options' => array_map(
                static fn (PriceOptionData $option): array => $option->toArray(),
                $this->priceOptions,
            ),
            'last_purchase_price_net' => $this->lastPurchasePriceNet,
            'tax_rate' => $this->taxRate,
            'manufacturer' => $this->manufacturer,
            'manufacturer_label' => $this->manufacturerLabel,
            'product_group_id' => $this->productGroupId,
            'product_group_name' => $this->productGroupName,
        ];
    }
}
