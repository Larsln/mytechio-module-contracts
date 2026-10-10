<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * Read-only view of a catalog article in the core.
 *
 * `priceB2cNet` and `priceB2bNet` are the article's default net prices as
 * decimal strings with up to 4 decimals, never floats; price options follow in
 * a later minor release. `unit` is the core unit code printed on document
 * lines (e.g. `"Stk"`, `"Std"`), `unitName` its display name (e.g. `"Stück"`)
 * and `unitUneceCode` the UN/ECE Recommendation 20 code (e.g. `"H87"`,
 * `"HUR"`), or `null` when the unit has none. `stockTracked` tells whether the
 * core tracks the stock of the article.
 */
final readonly class ArticleData
{
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
    ) {
        Validate::decimal($priceB2cNet, 'priceB2cNet');
        Validate::decimal($priceB2bNet, 'priceB2bNet');
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
        ];
    }
}
