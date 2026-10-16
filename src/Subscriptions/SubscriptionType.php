<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * A subscription type provided by a plugin module (e.g. `domain`, `licence`,
 * `hosting`); the core itself ships only the type `generic`. Implementations
 * are collected via the container tag `mytechio.subscription_types`.
 *
 * A type is declarative: it describes its fields via `fields()`, the core
 * renders the form from them and validates the input server-side. A type
 * never owns money, tax or booking logic. When the field schema changes,
 * `schemaVersion()` is bumped; the core keeps old values, and new required
 * fields only apply to new or edited subscriptions. If the plugin providing a
 * type is removed, its subscriptions stay billable and become read-only.
 *
 * The hooks run after the core committed the change. Types that need none of
 * them, and no custom input component, `use SubscriptionTypeDefaults`.
 */
interface SubscriptionType
{
    /**
     * Unique key across all modules, e.g. `"domain"`.
     */
    public function key(): string;

    /**
     * German UI label, e.g. `"Domain"`.
     */
    public function label(): string;

    /**
     * Version of the field schema; bump it whenever `fields()` changes.
     */
    public function schemaVersion(): int;

    /**
     * @return list<SubscriptionField>
     */
    public function fields(): array;

    /**
     * Whether a subscription of this type stands for exactly one object (a
     * domain, a server): the quantity is then always 1 and the core neither
     * offers nor accepts another one. Types whose quantity multiplies the
     * price (licence seats, hours) return false.
     */
    public function singleObject(): bool;

    /**
     * Optional frontend component of the module that replaces the generated
     * form.
     */
    public function inputComponent(): ?string;

    public function created(SubscriptionData $subscription): void;

    public function cancelled(SubscriptionData $subscription): void;

    public function ended(SubscriptionData $subscription): void;
}
