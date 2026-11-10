<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * The type-field values for the component subscription of one component article
 * of a package (see `NewSubscription::$components`). `attributes` follow the
 * schema of the component's `SubscriptionType`; the core validates them.
 * `source` and `externalId` bind the component subscription to a provider
 * source entry like `NewSubscription::$source` / `$externalId` do.
 */
final readonly class PackageComponentInput
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $articleId,
        public array $attributes = [],
        public ?string $source = null,
        public ?string $externalId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'attributes' => $this->attributes,
            'source' => $this->source,
            'external_id' => $this->externalId,
        ];
    }
}
