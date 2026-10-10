<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Source of subscription suggestions for the subscription picker — modules
 * that manage external objects (e.g. domains at a registrar) offer a search
 * through this interface, without the core needing to have already created
 * these subscriptions. Implementations are collected via the container tag
 * `mytechio.subscription_sources`.
 *
 * Action methods (`setAutorenew()`, `transferToCompany()`, `releaseTransfer()`)
 * are only called for keys listed in `capabilities()`. Sources that support no
 * actions `use SubscriptionSourceDefaults`, whose implementations throw a
 * `ContractException`.
 */
interface SubscriptionSource
{
    /**
     * Unique key of the module, e.g. `"domainrobot"`.
     */
    public function sourceKey(): string;

    /**
     * @return list<string> Subscription types offered by this source, e.g. `["domain"]`.
     */
    public function subscriptionTypes(): array;

    /**
     * @return list<SubscriptionSuggestion>
     */
    public function suggest(string $type, string $query, int $limit = 20): array;

    /**
     * Actions this source can perform: any of `"autorenew"`, `"owner_change"`,
     * `"transfer_out"`. The caller offers only the actions listed here.
     *
     * @return list<string>
     */
    public function capabilities(): array;

    /**
     * Switches the source's automatic renewal on or off (capability
     * `"autorenew"`).
     */
    public function setAutorenew(string $externalId, bool $enabled, ActorRef $actor): SubscriptionActionRef;

    /**
     * Changes the owner of the object at the source to the operating company
     * (capability `"owner_change"`).
     */
    public function transferToCompany(string $externalId, ActorRef $actor): SubscriptionActionRef;

    /**
     * Releases the object for transfer-out, e.g. by creating an auth code
     * (capability `"transfer_out"`).
     */
    public function releaseTransfer(string $externalId, ActorRef $actor): SubscriptionActionRef;
}
