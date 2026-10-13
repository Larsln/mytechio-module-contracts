<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Ai;

/**
 * Resolver for the active {@see StructuredCompletion} provider. The core binds
 * it in the container (`app(StructuredCompletions::class)`); a module asks this
 * contract instead of enumerating the tag `mytechio.structured_completions`
 * itself, so it sees exactly the provider of the module that holds the
 * exclusive capability `structured_completion` and is enabled, compatible and
 * configured.
 *
 * A feature must keep working without a provider: it checks {@see self::available()}
 * and hides its AI entry point when that is `false`.
 */
interface StructuredCompletions
{
    /**
     * The provider in use, or `null` when no enabled module provides one or the
     * holder is not configured (e.g. API key missing).
     */
    public function current(): ?StructuredCompletion;

    /**
     * Whether {@see self::current()} returns a provider.
     */
    public function available(): bool;
}
