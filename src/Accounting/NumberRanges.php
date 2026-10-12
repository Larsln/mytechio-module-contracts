<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * The core's number ranges (document and identifier numbers): reads, the
 * gapless draw and the scheduled format changes.
 *
 * A range is addressed by its type, the string value of the core's
 * `NumberRangeType` (`invoice`, `incoming_invoice`, `company_asset`, …); every
 * core type is available. An unknown type raises
 * `NumberRangeNotFoundException`. `next()` and `update()` fire their domain
 * event (`NumberDrawn`, `NumberRangeUpdated`) after the commit.
 */
interface NumberRanges
{
    /**
     * Every range, ordered the way the core lists them.
     *
     * @return list<NumberRangeData>
     */
    public function list(): array;

    public function find(string $type): ?NumberRangeData;

    /**
     * The number `next()` would return for a document year, without
     * reserving it.
     *
     * @throws NumberRangeNotFoundException
     */
    public function preview(string $type, int $year): string;

    /**
     * Reserves and returns the next formatted number of a range for a
     * document year. The draw is gapless: it runs in its own transaction with
     * a row lock on the counter, so a rollback of the caller's surrounding
     * transaction returns the number — call it inside the transaction that
     * stores the numbered document, never before it.
     *
     * @throws NumberRangeNotFoundException
     */
    public function next(string $type, int $year): string;

    /**
     * Schedules a format version for the turn of a future year (or replaces
     * the version already scheduled for that year). Identifier ranges
     * (`isLocked`) cannot change, the year cannot be dropped from a range
     * once it issued numbers, and ranges that share a document column
     * (invoice, credit note, cancellation) need a prefix no sibling owns.
     *
     * @throws NumberRangeNotFoundException
     * @throws ContractException with a German message if a rule above is violated.
     */
    public function update(string $type, NumberRangeSettings $settings, ActorRef $actor): NumberRangeData;

    /**
     * Discards a scheduled format version that has not taken effect yet.
     *
     * @throws NumberRangeNotFoundException
     * @throws ContractException if the year is not in the future.
     */
    public function discardScheduled(string $type, int $validFromYear, ActorRef $actor): NumberRangeData;
}
