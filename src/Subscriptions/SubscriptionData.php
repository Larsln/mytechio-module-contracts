<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;

/**
 * Read-only view of a subscription (German UI: "Leistung") in the core: a
 * service or product a contact receives per period, created from a period
 * price option of an article.
 *
 * `quantity` is a decimal string, dates are `YYYY-MM-DD`; the constructor
 * rejects malformed values with a `ContractException`. `endsOn` is the last
 * day the subscription runs (for status `Cancelled` the end of the notice
 * period), `cancelledOn` the day the notice was received, `billedUntil` the
 * last day covered by an invoice.
 *
 * `source` is the key of an external source (e.g. `"domainrobot"`) and
 * `externalId` the identifier there (e.g. the domain name); both are set or
 * both are `null`. `providerName` is the free-text provider of a subscription
 * without a source (e.g. `"IONOS"`). `renewsOn`, `autorenew` and
 * `externalStatus` come from the source's connector (`updateFromSource()`)
 * when set.
 *
 * `attributes` are the type-specific fields according to the schema of the
 * subscription's `SubscriptionType`. Secret fields are delivered decrypted:
 * modules are trusted code and must never log them.
 */
final readonly class SubscriptionData
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $id,
        public int $contactId,
        public string $type,
        public string $label,
        public SubscriptionStatus $status,
        public ?int $articleId,
        public ?int $priceOptionId,
        public string $quantity,
        public string $startsOn,
        public ?string $endsOn = null,
        public ?string $cancelledOn = null,
        public ?string $billedUntil = null,
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $providerName = null,
        public ?string $renewsOn = null,
        public ?bool $autorenew = null,
        public ?string $externalStatus = null,
        public array $attributes = [],
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::date($startsOn, 'startsOn');

        foreach (['endsOn' => $endsOn, 'cancelledOn' => $cancelledOn, 'billedUntil' => $billedUntil, 'renewsOn' => $renewsOn] as $field => $date) {
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
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'type' => $this->type,
            'label' => $this->label,
            'status' => $this->status->value,
            'article_id' => $this->articleId,
            'price_option_id' => $this->priceOptionId,
            'quantity' => $this->quantity,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'cancelled_on' => $this->cancelledOn,
            'billed_until' => $this->billedUntil,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'provider_name' => $this->providerName,
            'renews_on' => $this->renewsOn,
            'autorenew' => $this->autorenew,
            'external_status' => $this->externalStatus,
            'attributes' => $this->attributes,
        ];
    }
}
