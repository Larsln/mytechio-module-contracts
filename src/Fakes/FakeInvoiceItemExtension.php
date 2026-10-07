<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Invoices\InvoiceItemExtension;

/**
 * Test double for `InvoiceItemExtension`: records every call
 * (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`,
 * `duplicateCalls`), so module tests can verify what the core
 * passed in. `withValidationErrors()` and `withAnnotationLines()`
 * configure the return values; `duplicate()` returns the passed-in
 * extras unchanged by default.
 */
final class FakeInvoiceItemExtension implements InvoiceItemExtension
{
    /**
     * @var list<array{extras: array<string, mixed>, contactId: ?int}>
     */
    public array $validateCalls = [];

    /**
     * @var list<array{invoiceId: int, contactId: ?int, items: list<array{id: int, extras: array<string, mixed>}>}>
     */
    public array $afterItemsSyncedCalls = [];

    /**
     * @var list<array{invoiceItemId: int, extras: array<string, mixed>}>
     */
    public array $annotateCalls = [];

    /**
     * @var list<array<string, mixed>>
     */
    public array $duplicateCalls = [];

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

    public function validate(array $extras, ?int $contactId): array
    {
        $this->validateCalls[] = ['extras' => $extras, 'contactId' => $contactId];

        return $this->validationErrors;
    }

    public function afterItemsSynced(int $invoiceId, ?int $contactId, array $items): void
    {
        $this->afterItemsSyncedCalls[] = ['invoiceId' => $invoiceId, 'contactId' => $contactId, 'items' => $items];
    }

    public function annotate(int $invoiceItemId, array $extras): array
    {
        $this->annotateCalls[] = ['invoiceItemId' => $invoiceItemId, 'extras' => $extras];

        return $this->annotationLines;
    }

    public function duplicate(array $extras): array
    {
        $this->duplicateCalls[] = $extras;

        return $extras;
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
