<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Ai;

/**
 * Text-in, JSON-out model call for features that need a structured answer
 * (e.g. a draft for a CSV profile). Container tag
 * `mytechio.structured_completions`; the capability `structured_completion`
 * is exclusive, so at most one enabled module provides it. The core
 * resolves the provider; a feature must work (or hide its AI button) when
 * none is active.
 *
 * Callers pass only data they have already made safe: `$untrustedData` is
 * treated as data, never as instructions, but providers cannot guarantee
 * that — a caller must not send content it would not want a third party to
 * steer.
 */
interface StructuredCompletion
{
    /**
     * Unique key of the provider == module name, e.g. `"extraction-anthropic"`.
     */
    public function completionKey(): string;

    /**
     * Display name for the module overview, e.g. `"Anthropic Claude"`.
     */
    public function label(): string;

    /**
     * Whether the provider is ready to use (e.g. API key configured).
     */
    public function configured(): bool;

    /**
     * Single call without tools. The provider sends `$instruction` as the
     * instruction and `$untrustedData` clearly delimited as data, and binds
     * the output to `$jsonSchema` through the vendor's structured-output
     * feature. No second call, no follow-up questions.
     *
     * @param  array<string, mixed>  $jsonSchema  JSON Schema (draft 2020-12 subset) of the expected answer
     * @return array<string, mixed> decoded answer; the caller validates it again against its own rules
     *
     * @throws StructuredCompletionException
     */
    public function complete(string $instruction, string $untrustedData, array $jsonSchema): array;
}
