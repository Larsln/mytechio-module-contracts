<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;

/**
 * Data for creating a new subscription (German UI: "Leistung").
 *
 * `priceOptionId` selects a period price option of the article and therefore
 * requires `articleId`. `quantity` is a decimal string greater than zero.
 * `startsOn` (`YYYY-MM-DD`) defaults to today when `null`; the core resolves
 * it. `source`/`externalId` bind the subscription to an external source
 * directly at creation (e.g. via a connector import) and are set together or
 * not at all; `providerName` is the free-text provider of a subscription
 * without a source. `attributes` are the type-specific fields according to the
 * schema of the `SubscriptionType`; the core validates them server-side.
 */
final readonly class NewSubscription
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $contactId,
        public string $type,
        public string $label,
        public ?int $articleId = null,
        public ?int $priceOptionId = null,
        public string $quantity = '1',
        public ?string $startsOn = null,
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $providerName = null,
        public ?string $renewsOn = null,
        public ?bool $autorenew = null,
        public array $attributes = [],
    ) {
        if ($priceOptionId !== null && $articleId === null) {
            throw new ContractException('priceOptionId requires articleId.');
        }

        Validate::decimal($quantity, 'quantity');

        if (str_starts_with($quantity, '-') || preg_match('/^0*(\.0+)?$/', $quantity) === 1) {
            throw new ContractException("quantity must be greater than zero, got \"{$quantity}\".");
        }

        foreach (['startsOn' => $startsOn, 'renewsOn' => $renewsOn] as $field => $date) {
            if ($date !== null) {
                Validate::date($date, $field);
            }
        }

        if (($source === null) !== ($externalId === null)) {
            throw new ContractException('source and externalId must either both be set or both be null.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'type' => $this->type,
            'label' => $this->label,
            'article_id' => $this->articleId,
            'price_option_id' => $this->priceOptionId,
            'quantity' => $this->quantity,
            'starts_on' => $this->startsOn,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'provider_name' => $this->providerName,
            'renews_on' => $this->renewsOn,
            'autorenew' => $this->autorenew,
            'attributes' => $this->attributes,
        ];
    }
}
