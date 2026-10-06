<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Zugriff auf das Journal (Buchungssätze, Kontenplan) aus Modulen heraus.
 *
 * Kern-Semantik: Soll=Haben wird vom Kern erzwungen
 * (`UnbalancedEntryException`), festgeschriebene Perioden lehnen neue
 * Buchungen ab (`PeriodClosedException`). Module buchen niemals direkt
 * gegen das Ledger-Paket — jede Buchung läuft über diesen Vertrag.
 */
interface Journal
{
    /**
     * @throws UnbalancedEntryException wenn Soll ≠ Haben.
     * @throws PeriodClosedException wenn die Periode bereits festgeschrieben ist.
     */
    public function post(JournalEntryDraft $draft): JournalEntryRef;

    public function reverse(string $entryId, string $bookedOn, string $reason, ActorRef $actor): JournalEntryRef;

    public function findAccount(string $accountNumber): ?AccountInfo;
}
