<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\ExtractionContextData;
use MyTechIO\Contracts\Documents\ExtractionFailedException;
use MyTechIO\Contracts\Documents\InvoiceExtractor;

/**
 * Test double for `InvoiceExtractor`: returns a predefined payload (or
 * throws a predefined `ExtractionFailedException`), counts every call
 * to `extract()`.
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
     * Sample payload with `source_box` on two fields (supplier name and
     * invoice number); all other leaves omit it.
     *
     * @return array<string, mixed>
     */
    public static function samplePayload(): array
    {
        return [
            'supplier' => [
                'name' => [
                    'value' => 'ACME GmbH',
                    'source_text' => 'ACME GmbH',
                    'page' => 1,
                    'source_box' => ['x0' => 0.08, 'y0' => 0.05, 'x1' => 0.31, 'y1' => 0.07],
                ],
            ],
            'invoice' => [
                'number' => [
                    'value' => 'RE-2026-0042',
                    'source_text' => 'RE-2026-0042',
                    'page' => 1,
                    'source_box' => ['x0' => 0.62, 'y0' => 0.41, 'x1' => 0.83, 'y1' => 0.43],
                ],
                'date' => [
                    'value' => '2026-10-01',
                    'source_text' => '01.10.2026',
                    'page' => 1,
                ],
            ],
            'meta' => ['model' => 'fake-model'],
        ];
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
