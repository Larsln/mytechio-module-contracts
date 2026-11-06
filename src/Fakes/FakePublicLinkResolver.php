<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Web\LinkTarget;
use MyTechIO\Contracts\Web\PublicLinkData;
use MyTechIO\Contracts\Web\PublicLinkResolver;

/**
 * {@see PublicLinkResolver} for tests: answers every link of its kind with the
 * configured target and records the links it was asked for.
 */
final class FakePublicLinkResolver implements PublicLinkResolver
{
    /** @var list<PublicLinkData> */
    public array $resolved = [];

    public function __construct(
        private readonly string $kind = 'fake.link',
        private readonly string $label = 'Test-Link',
        private ?LinkTarget $target = null,
    ) {}

    public function answerWith(LinkTarget $target): void
    {
        $this->target = $target;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function resolve(PublicLinkData $link): LinkTarget
    {
        $this->resolved[] = $link;

        return $this->target ?? LinkTarget::redirect('https://pay.example.test/session');
    }
}
