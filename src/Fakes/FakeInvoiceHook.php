<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\HookWarning;
use MyTechIO\Contracts\Invoices\InvoiceHook;
use MyTechIO\Contracts\Invoices\InvoiceRef;

/**
 * Test double for `InvoiceHook`: records every call (`finalizedCalls`,
 * `cancelledCalls`) so module tests can verify what the core passed in.
 * `returnWarnings()` sets what `afterFinalized()` hands back.
 */
final class FakeInvoiceHook implements InvoiceHook
{
    /**
     * @var list<array{invoice: InvoiceRef, actor: ActorRef}>
     */
    public array $finalizedCalls = [];

    /**
     * @var list<array{original: InvoiceRef, cancellation: InvoiceRef, actor: ActorRef}>
     */
    public array $cancelledCalls = [];

    /**
     * @var list<HookWarning>
     */
    private array $warnings = [];

    public function afterFinalized(InvoiceRef $invoice, ActorRef $actor): array
    {
        $this->finalizedCalls[] = ['invoice' => $invoice, 'actor' => $actor];

        return $this->warnings;
    }

    public function afterCancelled(InvoiceRef $original, InvoiceRef $cancellation, ActorRef $actor): void
    {
        $this->cancelledCalls[] = ['original' => $original, 'cancellation' => $cancellation, 'actor' => $actor];
    }

    /**
     * @param  list<HookWarning>  $warnings
     */
    public function returnWarnings(array $warnings): void
    {
        $this->warnings = $warnings;
    }
}
