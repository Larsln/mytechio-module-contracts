<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * Read-only view of one price list entry.
 *
 * At most one of `productGroupId`, `articleId` and `priceOptionId` names the
 * target; without any the entry applies to everything on the list. An entry
 * carries exactly one of `fixedPriceNet` (decimal string, only for an article
 * or a price option) and `discountPercent` (decimal string, e.g. `'12.50'`).
 */
final readonly class PriceListEntryData
{
    public function __construct(
        public int $id,
        public int $priceListId,
        public ?int $productGroupId,
        public ?int $articleId,
        public ?int $priceOptionId,
        public ?string $fixedPriceNet,
        public ?string $discountPercent,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'price_list_id' => $this->priceListId,
            'product_group_id' => $this->productGroupId,
            'article_id' => $this->articleId,
            'price_option_id' => $this->priceOptionId,
            'fixed_price_net' => $this->fixedPriceNet,
            'discount_percent' => $this->discountPercent,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  shape of `toArray()`
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            priceListId: $data['price_list_id'],
            productGroupId: $data['product_group_id'] ?? null,
            articleId: $data['article_id'] ?? null,
            priceOptionId: $data['price_option_id'] ?? null,
            fixedPriceNet: $data['fixed_price_net'] ?? null,
            discountPercent: $data['discount_percent'] ?? null,
        );
    }
}
