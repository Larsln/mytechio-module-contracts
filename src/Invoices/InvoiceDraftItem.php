<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Eine Position eines Rechnungs-Entwurfs.
 *
 * `quantity`, `unitPriceNet` und `discountPercent` sind Strings (4
 * Nachkommastellen), nie Float. `customerAssetIds` verknüpft die Position
 * mit bestehenden Kundenobjekten (z. B. der Domain, die abgerechnet wird).
 */
final readonly class InvoiceDraftItem
{
    /**
     * @param  list<int>  $customerAssetIds
     */
    public function __construct(
        public string $description,
        public string $quantity,
        public string $unitPriceNet,
        public ?string $unit = null,
        public ?int $articleId = null,
        public ?int $taxCategoryId = null,
        public string $discountPercent = '0',
        public array $customerAssetIds = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price_net' => $this->unitPriceNet,
            'unit' => $this->unit,
            'article_id' => $this->articleId,
            'tax_category_id' => $this->taxCategoryId,
            'discount_percent' => $this->discountPercent,
            'customer_asset_ids' => $this->customerAssetIds,
        ];
    }
}
