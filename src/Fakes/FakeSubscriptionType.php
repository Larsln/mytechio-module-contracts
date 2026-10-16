<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionField;
use MyTechIO\Contracts\Subscriptions\SubscriptionType;

/**
 * Test double for `SubscriptionType`: configurable key, label, field schema,
 * schema version, input component and single-object flag. Every hook call is recorded in
 * `calls()` with the hook name and the ID of the subscription.
 */
final class FakeSubscriptionType implements SubscriptionType
{
    /**
     * @var list<array{hook: string, id: int}>
     */
    private array $calls = [];

    /**
     * @param  list<SubscriptionField>  $fields
     */
    public function __construct(
        private readonly string $key = 'fake',
        private readonly string $label = 'Fake',
        private readonly array $fields = [],
        private readonly int $schemaVersion = 1,
        private readonly ?string $inputComponent = null,
        private readonly bool $singleObject = false,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function schemaVersion(): int
    {
        return $this->schemaVersion;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function singleObject(): bool
    {
        return $this->singleObject;
    }

    public function inputComponent(): ?string
    {
        return $this->inputComponent;
    }

    public function created(SubscriptionData $subscription): void
    {
        $this->calls[] = ['hook' => 'created', 'id' => $subscription->id];
    }

    public function cancelled(SubscriptionData $subscription): void
    {
        $this->calls[] = ['hook' => 'cancelled', 'id' => $subscription->id];
    }

    public function ended(SubscriptionData $subscription): void
    {
        $this->calls[] = ['hook' => 'ended', 'id' => $subscription->id];
    }

    /**
     * Recorded hook calls in order.
     *
     * @return list<array{hook: string, id: int}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
