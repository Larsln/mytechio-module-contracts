<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * A suggestion from a `SubscriptionSource` module for the subscription
 * picker.
 *
 * `externalId` is the key the module itself uses (e.g. a domain name) — not
 * necessarily the ID of a subscription in the core, since the subscription
 * may not have been created yet.
 */
final readonly class SubscriptionSuggestion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $source,
        public string $type,
        public string $externalId,
        public string $label,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'type' => $this->type,
            'external_id' => $this->externalId,
            'label' => $this->label,
            'attributes' => $this->attributes,
        ];
    }
}
