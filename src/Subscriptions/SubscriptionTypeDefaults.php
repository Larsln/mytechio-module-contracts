<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * Default implementations of the optional `SubscriptionType` members: no
 * custom input component, all hooks do nothing. Types `use` this trait and
 * override what they need.
 */
trait SubscriptionTypeDefaults
{
    public function singleObject(): bool
    {
        return false;
    }

    public function inputComponent(): ?string
    {
        return null;
    }

    public function created(SubscriptionData $subscription): void {}

    public function cancelled(SubscriptionData $subscription): void {}

    public function ended(SubscriptionData $subscription): void {}
}
