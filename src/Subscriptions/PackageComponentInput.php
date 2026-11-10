<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * The type-field values for the component subscription of one component article
 * of a package (see `NewSubscription::$components`). `attributes` follow the
 * schema of the component's `SubscriptionType`; the core validates them.
 */
final readonly class PackageComponentInput
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public int $articleId,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'attributes' => $this->attributes,
        ];
    }
}
