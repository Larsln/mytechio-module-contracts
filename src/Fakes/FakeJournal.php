<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\AccountInfo;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\Journal;
use MyTechIO\Contracts\Accounting\JournalEntryDraft;
use MyTechIO\Contracts\Accounting\JournalEntryRef;
use MyTechIO\Contracts\Accounting\JournalLineDraft;
use MyTechIO\Contracts\Accounting\Side;
use MyTechIO\Contracts\Accounting\UnbalancedEntryException;
use MyTechIO\Contracts\ContractException;

/**
 * Test-Double für `Journal`: hält gebuchte Entwürfe in-memory,
 * prüft die Soll=Haben-Invariante wie die Kern-Implementierung und
 * erlaubt das Vorbelegen von Konten über `seedAccount()`.
 *
 * Geldbeträge bleiben durchgehend Strings (kein Float!) — die
 * Balance-Prüfung rechnet in Minor-Units (Dezimalstring × 10.000) mit
 * reiner Integer-Arithmetik.
 */
final class FakeJournal implements Journal
{
    /**
     * @var array<string, AccountInfo>
     */
    private array $accounts = [];

    /**
     * @var array<string, JournalEntryDraft>
     */
    private array $draftsByEntryId = [];

    /**
     * @var list<JournalEntryRef>
     */
    private array $posted = [];

    private int $nextEntryId = 1;

    public function seedAccount(AccountInfo $account): void
    {
        $this->accounts[$account->number] = $account;
    }

    public function post(JournalEntryDraft $draft): JournalEntryRef
    {
        $this->assertBalanced($draft);

        $id = (string) $this->nextEntryId++;
        $entryNumber = sprintf('F-%04d', (int) $id);

        $ref = new JournalEntryRef($id, $entryNumber, $draft->bookedOn);

        $this->draftsByEntryId[$id] = $draft;
        $this->posted[] = $ref;

        return $ref;
    }

    public function reverse(string $entryId, string $bookedOn, string $reason, ActorRef $actor): JournalEntryRef
    {
        $original = $this->draftsByEntryId[$entryId] ?? null;

        if ($original === null) {
            throw new ContractException(sprintf('Buchungssatz %s ist unbekannt.', $entryId));
        }

        $reversedLines = array_map(
            static fn (JournalLineDraft $line): JournalLineDraft => new JournalLineDraft(
                accountNumber: $line->accountNumber,
                side: $line->side === Side::Debit ? Side::Credit : Side::Debit,
                amount: $line->amount,
                taxCode: $line->taxCode,
                memo: $line->memo,
            ),
            $original->lines,
        );

        return $this->post(new JournalEntryDraft(
            bookedOn: $bookedOn,
            description: sprintf('Storno zu %s: %s', $entryId, $reason),
            lines: $reversedLines,
            sourceType: $original->sourceType,
            sourceId: $original->sourceId,
            actor: $actor,
        ));
    }

    public function findAccount(string $accountNumber): ?AccountInfo
    {
        return $this->accounts[$accountNumber] ?? null;
    }

    /**
     * @return list<JournalEntryRef>
     */
    public function posted(): array
    {
        return $this->posted;
    }

    private function assertBalanced(JournalEntryDraft $draft): void
    {
        $debit = 0;
        $credit = 0;

        foreach ($draft->lines as $line) {
            $minorUnits = self::toMinorUnits($line->amount);

            if ($line->side === Side::Debit) {
                $debit += $minorUnits;
            } else {
                $credit += $minorUnits;
            }
        }

        if ($debit !== $credit) {
            throw new UnbalancedEntryException(self::fromMinorUnits($debit), self::fromMinorUnits($credit));
        }
    }

    /**
     * Wandelt einen Dezimal-String (bis zu 4 Nachkommastellen) in Minor-Units
     * (× 10.000) um — ausschließlich mit String-/Integer-Operationen, damit
     * keine Float-Rundungsfehler entstehen.
     */
    private static function toMinorUnits(string $amount): int
    {
        $amount = trim($amount);
        $negative = str_starts_with($amount, '-');

        if ($negative) {
            $amount = substr($amount, 1);
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 4), 4, '0');
        $whole = $whole === '' ? '0' : $whole;

        $minorUnits = ((int) $whole) * 10000 + (int) $fraction;

        return $negative ? -$minorUnits : $minorUnits;
    }

    private static function fromMinorUnits(int $minorUnits): string
    {
        $negative = $minorUnits < 0;
        $minorUnits = abs($minorUnits);

        $whole = intdiv($minorUnits, 10000);
        $fraction = $minorUnits % 10000;

        return ($negative ? '-' : '').$whole.'.'.str_pad((string) $fraction, 4, '0', STR_PAD_LEFT);
    }
}
