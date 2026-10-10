<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * Default implementations of the `SubscriptionSource` actions: no
 * capabilities, every action throws. Sources `use` this trait and override
 * what they support.
 */
trait SubscriptionSourceDefaults
{
    /**
     * @return list<string>
     */
    public function capabilities(): array
    {
        return [];
    }

    public function setAutorenew(string $externalId, bool $enabled, ActorRef $actor): SubscriptionActionRef
    {
        throw new ContractException('Aktion wird von dieser Quelle nicht unterstützt.');
    }

    public function transferToCompany(string $externalId, ActorRef $actor): SubscriptionActionRef
    {
        throw new ContractException('Aktion wird von dieser Quelle nicht unterstützt.');
    }

    public function releaseTransfer(string $externalId, ActorRef $actor): SubscriptionActionRef
    {
        throw new ContractException('Aktion wird von dieser Quelle nicht unterstützt.');
    }
}
