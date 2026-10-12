<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use DateTimeInterface;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * Stock keeping (Bedarfsführung): quantity ledger, reservations, stock
 * vouchers (Lagerbelege) and the cost of goods sold. The core implements it
 * until the stock module takes over; a module that needs the stock position
 * (pricing, analytics, document hooks) only ever talks to this contract.
 *
 * Quantities are decimal strings with up to 4 decimals, amounts are net
 * decimal strings, dates `Y-m-d` (date-times ISO 8601); never floats. Every
 * rule violation is a `ContractException` with a German message, an unknown
 * article a `StockArticleNotFoundException`, an unknown voucher a
 * `StockVoucherNotFoundException`. Every mutation fires one domain event
 * (`StockMovementRecorded`, `StockVoucherCreated`, `StockVoucherReversed`,
 * `StockSettingsChanged`; `LowStockDetected` when an article falls to or
 * below its reorder point).
 *
 * The ledger is append-only: nothing is edited or deleted, a correction is a
 * counter-movement or a reversal voucher.
 */
interface Stock
{
    /**
     * @throws StockArticleNotFoundException
     */
    public function level(int $articleId): StockLevel;

    /**
     * Unknown article IDs are left out of the result.
     *
     * @param  list<int>  $articleIds
     * @return array<int, StockLevel> Keyed by article ID.
     */
    public function levels(array $articleIds): array;

    /**
     * The levels of all tracked articles at or below their reorder point
     * (low-stock warning on), ordered by article ID.
     *
     * @return list<StockLevel>
     */
    public function lowStock(): array;

    public function movements(StockMovementFilter $filter = new StockMovementFilter, int $page = 1, int $perPage = 50): StockMovementPage;

    /**
     * The not-reversed goods receipts of an article that carry a cost,
     * optionally only those on or after `$since`; newest first.
     *
     * @return list<StockMovementData>
     */
    public function receipts(int $articleId, ?DateTimeInterface $since = null): array;

    public function vouchers(StockVoucherFilter $filter = new StockVoucherFilter, int $page = 1, int $perPage = 25): StockVoucherPage;

    public function findVoucher(string $number): ?StockVoucherData;

    /**
     * Cost of goods sold per article and month between the two dates
     * (inclusive), ordered by month, then article ID.
     *
     * @return list<CogsRow>
     */
    public function costOfGoodsSold(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Books a goods receipt (an `incoming` movement).
     *
     * @throws ContractException if the quantity is not positive or the article is not stock-tracked.
     */
    public function recordReceipt(NewStockReceipt $receipt, ActorRef $actor): StockMovementData;

    /**
     * Books a sale (an `outgoing_sale` movement with a cost snapshot) or a
     * customer return. Overselling is allowed.
     *
     * @throws ContractException if the quantity is not positive or the article is not stock-tracked.
     */
    public function recordIssue(NewStockIssue $issue, ActorRef $actor): StockMovementData;

    /**
     * Reserves stock for an open order; changes only the reserved quantity.
     * Reservations may exceed the physical stock.
     *
     * @throws ContractException if the quantity is not positive or the article is not stock-tracked.
     */
    public function reserve(int $articleId, string $quantity, StockReference $reference): StockLevel;

    /**
     * Releases a reservation (never below zero).
     *
     * @throws ContractException if the quantity is not positive or the article is not stock-tracked.
     */
    public function release(int $articleId, string $quantity, StockReference $reference): StockLevel;

    /**
     * Writes stock off on a stock voucher (booked on the journal at the last
     * purchase price). The stock must not go negative.
     *
     * @throws ContractException if the type is not a write-off type, the quantity is not positive,
     *                           the reason is empty, the article is not stock-tracked or the stock is insufficient.
     */
    public function writeOff(NewWriteOff $writeOff, ActorRef $actor): StockVoucherData;

    /**
     * Sets the stock to the counted quantity on an inventory-count voucher;
     * `null` when the count equals the book stock (nothing is booked).
     *
     * @throws ContractException if the counted quantity is negative, the reason is empty or the article is not stock-tracked.
     */
    public function inventoryCount(NewInventoryCount $count, ActorRef $actor): ?StockVoucherData;

    /**
     * Cancels a voucher through a counter-voucher with negated movements and
     * a reversed booking; returns the counter-voucher.
     *
     * @throws StockVoucherNotFoundException
     * @throws ContractException if the voucher is itself a reversal, was already reversed or the reason is empty.
     */
    public function reverseVoucher(string $number, ActorRef $actor, string $reason): StockVoucherData;

    /**
     * @throws StockArticleNotFoundException
     * @throws ContractException if the warning is on without a reorder point or a value is malformed.
     */
    public function updateSettings(int $articleId, StockSettings $settings, ActorRef $actor): StockLevel;
}
