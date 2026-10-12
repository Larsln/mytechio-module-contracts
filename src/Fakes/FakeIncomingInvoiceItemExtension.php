<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Documents\IncomingInvoiceItemExtension;

/**
 * Test double for `IncomingInvoiceItemExtension`: records every call
 * (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`), so module tests
 * can verify what the core passed in. `withValidationErrors()` and
 * `withAnnotationLines()` configure the return values.
 */
final class FakeIncomingInvoiceItemExtension implements IncomingInvoiceItemExtension
{
    /**
     * @var list<array{extras: array<string, mixed>, supplierContactId: ?int}>
     */
    public array $validateCalls = [];

    /**
     * @var list<array{invoiceId: int, items: list<array{id: int, extras: array<string, mixed>}>}>
     */
    public array $afterItemsSyncedCalls = [];

    /**
     * @var list<array{incomingInvoiceItemId: int, extras: array<string, mixed>}>
     */
    public array $annotateCalls = [];

    /**
     * @var array<string, string>
     */
    private array $validationErrors = [];

    /**
     * @var list<string>
     */
    private array $annotationLines = [];

    public function __construct(
        private readonly string $extensionKey = 'fake',
    ) {}

    public function key(): string
    {
        return $this->extensionKey;
    }

    public function validate(array $extras, ?int $supplierContactId): array
    {
        $this->validateCalls[] = ['extras' => $extras, 'supplierContactId' => $supplierContactId];

        return $this->validationErrors;
    }

    public function afterItemsSynced(int $invoiceId, array $items): void
    {
        $this->afterItemsSyncedCalls[] = ['invoiceId' => $invoiceId, 'items' => $items];
    }

    public function annotate(int $incomingInvoiceItemId, array $extras): array
    {
        $this->annotateCalls[] = ['incomingInvoiceItemId' => $incomingInvoiceItemId, 'extras' => $extras];

        return $this->annotationLines;
    }

    /**
     * @param  array<string, string>  $errors
     */
    public function withValidationErrors(array $errors): void
    {
        $this->validationErrors = $errors;
    }

    /**
     * @param  list<string>  $lines
     */
    public function withAnnotationLines(array $lines): void
    {
        $this->annotationLines = $lines;
    }
}
