<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Web\LinkTarget;
use MyTechIO\Contracts\Web\NewPublicLink;
use MyTechIO\Contracts\Web\PublicLinkData;
use MyTechIO\Contracts\Web\PublicLinkResolver;
use MyTechIO\Contracts\Web\PublicLinks;
use MyTechIO\Contracts\Web\PublicLinkStatus;
use MyTechIO\Contracts\Web\PublicLinksUnavailableException;

/**
 * In-memory {@see PublicLinks} for module tests: tokens are predictable
 * (`link-1`, `link-2`, …), the public URL is `<baseUrl>/<token>`, and
 * resolvers are registered with {@see resolveWith()}. `follow()` behaves like
 * the real holder: status and expiry first, then the resolver, click count
 * and single use.
 */
final class FakePublicLinks implements PublicLinks
{
    /** @var array<string, PublicLinkData> */
    private array $links = [];

    /** @var array<string, PublicLinkResolver> */
    private array $resolvers = [];

    private int $sequence = 0;

    public function __construct(
        private bool $available = true,
        private readonly string $baseUrl = 'https://www.example.test/l',
        private readonly ?\DateTimeImmutable $now = null,
    ) {}

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    public function resolveWith(PublicLinkResolver $resolver): void
    {
        $this->resolvers[$resolver->kind()] = $resolver;
    }

    public function available(): bool
    {
        return $this->available;
    }

    public function create(NewPublicLink $link): PublicLinkData
    {
        if (! $this->available) {
            throw new PublicLinksUnavailableException('Kein Modul stellt öffentliche Links bereit.');
        }

        $token = 'link-'.(++$this->sequence);

        return $this->links[$token] = new PublicLinkData(
            token: $token,
            url: "{$this->baseUrl}/{$token}",
            kind: $link->kind,
            subjectType: $link->subjectType,
            subjectId: $link->subjectId,
            payload: $link->payload,
            status: PublicLinkStatus::Active,
            singleUse: $link->singleUse,
            expiresAt: $link->expiresAt,
            clickCount: 0,
            lastClickedAt: null,
            createdAt: $this->now(),
        );
    }

    public function find(string $token): ?PublicLinkData
    {
        return $this->links[$token] ?? null;
    }

    public function forSubject(string $kind, string $subjectType, string $subjectId): array
    {
        $matches = array_values(array_filter(
            $this->links,
            static fn (PublicLinkData $link): bool => $link->isActive()
                && $link->kind === $kind
                && $link->subjectType === $subjectType
                && $link->subjectId === $subjectId,
        ));

        return array_reverse($matches);
    }

    public function revoke(string $token, ?string $reason = null): void
    {
        $link = $this->links[$token] ?? null;

        if ($link !== null && $link->isActive()) {
            $this->links[$token] = $this->with($link, status: PublicLinkStatus::Revoked);
        }
    }

    public function revokeForSubject(string $kind, string $subjectType, string $subjectId, ?string $reason = null): int
    {
        $links = $this->forSubject($kind, $subjectType, $subjectId);

        foreach ($links as $link) {
            $this->revoke($link->token, $reason);
        }

        return count($links);
    }

    public function follow(string $token): LinkTarget
    {
        $link = $this->links[$token] ?? null;

        if ($link === null) {
            return LinkTarget::invalid('not_found', 'Dieser Link ist nicht bekannt.');
        }

        if ($link->isActive() && $link->expiresAt !== null && $link->expiresAt <= $this->now()) {
            $link = $this->links[$token] = $this->with($link, status: PublicLinkStatus::Expired);
        }

        if (! $link->isActive()) {
            return LinkTarget::invalid($link->status->value, 'Dieser Link ist nicht mehr gültig.');
        }

        $resolver = $this->resolvers[$link->kind] ?? null;

        if ($resolver === null) {
            return LinkTarget::invalid('no_resolver', 'Dieser Link kann derzeit nicht geöffnet werden.');
        }

        $target = $resolver->resolve($link);
        $this->links[$token] = $this->with(
            $link,
            status: $target->isRedirect() && $link->singleUse ? PublicLinkStatus::Used : $link->status,
            clickCount: $link->clickCount + 1,
            lastClickedAt: $this->now(),
        );

        return $target;
    }

    /**
     * @return list<PublicLinkData>
     */
    public function all(): array
    {
        return array_values($this->links);
    }

    private function with(PublicLinkData $link, ?PublicLinkStatus $status = null, ?int $clickCount = null, ?\DateTimeImmutable $lastClickedAt = null): PublicLinkData
    {
        return new PublicLinkData(
            token: $link->token,
            url: $link->url,
            kind: $link->kind,
            subjectType: $link->subjectType,
            subjectId: $link->subjectId,
            payload: $link->payload,
            status: $status ?? $link->status,
            singleUse: $link->singleUse,
            expiresAt: $link->expiresAt,
            clickCount: $clickCount ?? $link->clickCount,
            lastClickedAt: $lastClickedAt ?? $link->lastClickedAt,
            createdAt: $link->createdAt,
        );
    }

    private function now(): \DateTimeImmutable
    {
        return $this->now ?? new \DateTimeImmutable;
    }
}
