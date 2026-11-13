<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * What a return label costs the customer for a country (ISO 3166-1
 * alpha-2). `price` is a decimal string with two places, an end price.
 */
final readonly class ReturnOffer
{
    public function __construct(
        public string $country,
        public string $price,
        public string $currency = 'EUR',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['country' => $this->country, 'price' => $this->price, 'currency' => $this->currency];
    }
}
