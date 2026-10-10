<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionActionRef;
use MyTechIO\Contracts\Subscriptions\SubscriptionSource;
use MyTechIO\Contracts\Subscriptions\SubscriptionSuggestion;

/**
 * Test double for `SubscriptionSource`: configurable key, types, suggestions
 * and capabilities. Actions for a capability the fake does not list throw a
 * `ContractException` like the trait defaults; supported actions are recorded
 * in `calls()` and answer with `actionState()` (default `"done"`).
 */
final class FakeSubscriptionSource implements SubscriptionSource
{
    /**
     * @var list<array{action: string, externalId: string, enabled?: bool, actor: ActorRef}>
     */
    private array $calls = [];

    /**
     * @param  list<string>  $types
     * @param  list<string>  $capabilities
     * @param  list<SubscriptionSuggestion>  $suggestions
     * @param  'queued'|'done'|'unsupported'  $actionState
     */
    public function __construct(
        private string $key = 'fake',
        private array $types = ['domain'],
        private array $capabilities = [],
        private array $suggestions = [],
        private string $actionState = 'done',
    ) {}

    public function sourceKey(): string
    {
        return $this->key;
    }

    public function subscriptionTypes(): array
    {
        return $this->types;
    }

    public function suggest(string $type, string $query, int $limit = 20): array
    {
        $matches = array_filter(
            $this->suggestions,
            static fn (SubscriptionSuggestion $suggestion): bool => $suggestion->type === $type
                && ($query === '' || stripos($suggestion->label, $query) !== false),
        );

        return array_slice(array_values($matches), 0, $limit);
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param  list<string>  $capabilities
     */
    public function withCapabilities(array $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    public function seedSuggestion(SubscriptionSuggestion $suggestion): void
    {
        $this->suggestions[] = $suggestion;
    }

    /**
     * @param  'queued'|'done'|'unsupported'  $state
     */
    public function actionState(string $state): self
    {
        $this->actionState = $state;

        return $this;
    }

    public function setAutorenew(string $externalId, bool $enabled, ActorRef $actor): SubscriptionActionRef
    {
        $this->guard('autorenew');
        $this->calls[] = ['action' => 'set_autorenew', 'externalId' => $externalId, 'enabled' => $enabled, 'actor' => $actor];

        return $this->ref($externalId, 'set_autorenew');
    }

    public function transferToCompany(string $externalId, ActorRef $actor): SubscriptionActionRef
    {
        $this->guard('owner_change');
        $this->calls[] = ['action' => 'transfer_to_company', 'externalId' => $externalId, 'actor' => $actor];

        return $this->ref($externalId, 'transfer_to_company');
    }

    public function releaseTransfer(string $externalId, ActorRef $actor): SubscriptionActionRef
    {
        $this->guard('transfer_out');
        $this->calls[] = ['action' => 'release_transfer', 'externalId' => $externalId, 'actor' => $actor];

        return $this->ref($externalId, 'release_transfer');
    }

    /**
     * Recorded action calls in order.
     *
     * @return list<array{action: string, externalId: string, enabled?: bool, actor: ActorRef}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    private function guard(string $capability): void
    {
        if (! in_array($capability, $this->capabilities, true)) {
            throw new ContractException('Aktion wird von dieser Quelle nicht unterstützt.');
        }
    }

    private function ref(string $externalId, string $action): SubscriptionActionRef
    {
        return new SubscriptionActionRef(
            sourceKey: $this->key,
            externalId: $externalId,
            action: $action,
            jobReference: $this->actionState === 'queued' ? 'job-'.count($this->calls) : null,
            state: $this->actionState,
        );
    }
}
