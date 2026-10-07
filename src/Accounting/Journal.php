<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Access to the journal (journal entries, chart of accounts) from modules.
 *
 * Core semantics: debit=credit is enforced by the core
 * (`UnbalancedEntryException`), closed periods reject new bookings
 * (`PeriodClosedException`). Modules never book directly against the
 * ledger package — every booking goes through this contract.
 */
interface Journal
{
    /**
     * @throws UnbalancedEntryException if debit ≠ credit.
     * @throws PeriodClosedException if the period is already closed.
     */
    public function post(JournalEntryDraft $draft): JournalEntryRef;

    public function reverse(string $entryId, string $bookedOn, string $reason, ActorRef $actor): JournalEntryRef;

    public function findAccount(string $accountNumber): ?AccountInfo;
}
