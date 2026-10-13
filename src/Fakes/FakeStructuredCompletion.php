<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Ai\StructuredCompletion;
use MyTechIO\Contracts\Ai\StructuredCompletionException;

/**
 * Test double for `StructuredCompletion`: returns a predefined answer (or
 * throws a predefined `StructuredCompletionException`) and records every
 * call to `complete()`.
 */
final class FakeStructuredCompletion implements StructuredCompletion
{
    /**
     * @var list<array{instruction: string, data: string, schema: array<string, mixed>}>
     */
    private array $calls = [];

    /**
     * @param  array<string, mixed>  $answer
     */
    public function __construct(
        private string $completionKey = 'fake-completion',
        private string $label = 'Fake-KI',
        private bool $configured = true,
        private array $answer = [],
        private ?StructuredCompletionException $throws = null,
    ) {}

    public function completionKey(): string
    {
        return $this->completionKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function configured(): bool
    {
        return $this->configured;
    }

    public function complete(string $instruction, string $untrustedData, array $jsonSchema): array
    {
        $this->calls[] = ['instruction' => $instruction, 'data' => $untrustedData, 'schema' => $jsonSchema];

        if ($this->throws !== null) {
            throw $this->throws;
        }

        return $this->answer;
    }

    /**
     * @param  array<string, mixed>  $answer
     */
    public function returns(array $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function throws(StructuredCompletionException $exception): self
    {
        $this->throws = $exception;

        return $this;
    }

    public function withConfigured(bool $configured): self
    {
        $this->configured = $configured;

        return $this;
    }

    /**
     * @return list<array{instruction: string, data: string, schema: array<string, mixed>}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
