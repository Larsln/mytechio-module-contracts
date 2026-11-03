<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * Input of `PriceLists::addEntry()` and `PriceLists::updateEntry()`.
 *
 * Name at most one target (`productGroupId`, `articleId` or `priceOptionId`);
 * none means the whole list. Provide exactly one of `fixedPriceNet` (net
 * decimal string, needs an article or price option target) and
 * `discountPercent` (decimal string).
 */
final readonly class NewPriceListEntry
{
    public function __construct(
        public ?int $productGroupId = null,
        public ?int $articleId = null,
        public ?int $priceOptionId = null,
        public ?string $fixedPriceNet = null,
        public ?string $discountPercent = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_group_id' => $this->productGroupId,
            'article_id' => $this->articleId,
            'price_option_id' => $this->priceOptionId,
            'fixed_price_net' => $this->fixedPriceNet,
            'discount_percent' => $this->discountPercent,
        ];
    }
}
