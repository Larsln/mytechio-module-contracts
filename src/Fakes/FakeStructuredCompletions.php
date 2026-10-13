<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Ai\StructuredCompletion;
use MyTechIO\Contracts\Ai\StructuredCompletions;

/**
 * Test double for `StructuredCompletions`: serves one provider, or none.
 */
final class FakeStructuredCompletions implements StructuredCompletions
{
    public function __construct(
        private ?StructuredCompletion $provider = null,
    ) {}

    public function current(): ?StructuredCompletion
    {
        return $this->provider;
    }

    public function available(): bool
    {
        return $this->provider !== null;
    }

    public function with(?StructuredCompletion $provider): self
    {
        $this->provider = $provider;

        return $this;
    }
}
