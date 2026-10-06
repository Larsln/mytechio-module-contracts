<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\ExtractionContextData;
use MyTechIO\Contracts\Documents\ExtractionFailedException;
use MyTechIO\Contracts\Documents\InvoiceExtractor;

/**
 * Test-Double für `InvoiceExtractor`: liefert eine vorgegebene Payload (oder
 * wirft eine vorgegebene `ExtractionFailedException`), zählt jeden Aufruf
 * von `extract()`.
 */
final class FakeInvoiceExtractor implements InvoiceExtractor
{
    /**
     * @var list<array{blob: string, context: ExtractionContextData}>
     */
    private array $calls = [];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private string $extractorKey = 'fake-extraction',
        private string $label = 'Fake-Extraktion',
        private bool $configured = true,
        private array $payload = [],
        private ?ExtractionFailedException $throws = null,
    ) {}

    public function extractorKey(): string
    {
        return $this->extractorKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function configured(): bool
    {
        return $this->configured;
    }

    public function extract(string $pdfBlob, ExtractionContextData $context): array
    {
        $this->calls[] = ['blob' => $pdfBlob, 'context' => $context];

        if ($this->throws !== null) {
            throw $this->throws;
        }

        return $this->payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function returns(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function throws(ExtractionFailedException $exception): self
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
     * @return list<array{blob: string, context: ExtractionContextData}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
