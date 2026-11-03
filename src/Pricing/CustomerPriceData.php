<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * The price one contact pays for one price option.
 *
 * `listPriceNet` is the option's price for the contact's price class,
 * `unitPriceNet` the effective net unit price after the agreement (both
 * decimal strings with four places). `discountPercent` is the percentage
 * behind a percent source or the customer default, `'0'` for a fixed price or
 * none. The customer default does not change `unitPriceNet`: the core applies
 * it to the whole document.
 */
final readonly class CustomerPriceData
{
    public function __construct(
        public int $contactId,
        public int $priceOptionId,
        public string $listPriceNet,
        public string $unitPriceNet,
        public string $discountPercent,
        public CustomerPriceSource $source,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'price_option_id' => $this->priceOptionId,
            'list_price_net' => $this->listPriceNet,
            'unit_price_net' => $this->unitPriceNet,
            'discount_percent' => $this->discountPercent,
            'source' => $this->source->value,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  shape of `toArray()`
     */
    public static function fromArray(array $data): self
    {
        return new self(
            contactId: $data['contact_id'],
            priceOptionId: $data['price_option_id'],
            listPriceNet: $data['list_price_net'],
            unitPriceNet: $data['unit_price_net'],
            discountPercent: $data['discount_percent'],
            source: CustomerPriceSource::from($data['source']),
        );
    }
}
