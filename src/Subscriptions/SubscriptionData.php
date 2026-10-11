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
 * The terms are copied from the price option when the subscription is created
 * and never follow later edits of the option: `intervalUnit` (`"day"`, `"week"`,
 * `"month"` or `"year"`) with `intervalCount` is the billing period,
 * `minimumTermUntil` the last day of the minimum term (the earliest permitted `endsOn`),
 * `noticePeriodCount` with `noticePeriodUnit` the notice period.
 * `unitPriceNetOverride` is a decimal string that replaces the net unit price
 * of the price option; `null` means the option's price applies.
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
        public ?string $intervalUnit = null,
        public ?int $intervalCount = null,
        public ?string $unitPriceNetOverride = null,
        public ?string $minimumTermUntil = null,
        public ?int $noticePeriodCount = null,
        public ?string $noticePeriodUnit = null,
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::date($startsOn, 'startsOn');

        foreach (['endsOn' => $endsOn, 'cancelledOn' => $cancelledOn, 'billedUntil' => $billedUntil, 'renewsOn' => $renewsOn, 'minimumTermUntil' => $minimumTermUntil] as $field => $date) {
            if ($date !== null) {
                Validate::date($date, $field);
            }
        }

        if ($unitPriceNetOverride !== null) {
            Validate::decimal($unitPriceNetOverride, 'unitPriceNetOverride');
        }

        foreach (['intervalUnit' => $intervalUnit, 'noticePeriodUnit' => $noticePeriodUnit] as $field => $unit) {
            if ($unit !== null && ! in_array($unit, ['day', 'week', 'month', 'year'], true)) {
                throw new ContractException("{$field} must be 'day', 'week', 'month' or 'year', got \"{$unit}\".");
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
            'interval_unit' => $this->intervalUnit,
            'interval_count' => $this->intervalCount,
            'unit_price_net_override' => $this->unitPriceNetOverride,
            'minimum_term_until' => $this->minimumTermUntil,
            'notice_period_count' => $this->noticePeriodCount,
            'notice_period_unit' => $this->noticePeriodUnit,
        ];
    }
}
