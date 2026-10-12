<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * The company's fixed-asset register: read access, the schedule
 * (Anlagespiegel) and every write action.
 *
 * Source-document principle: assets arise from booked incoming invoices —
 * through the `IncomingInvoiceGuard` when an invoice is booked, or later via
 * `create()` for the inventory list. There is deliberately no free-hand
 * creation of accounting assets.
 *
 * Rule violations (a locked field, a disposal of an inventory-list asset, a
 * closed period, …) surface as `ContractException` with a German message;
 * an unknown ID as `FixedAssetNotFoundException`. Every write action fires
 * its domain event (`FixedAssetCreated`, `FixedAssetActivated`,
 * `FixedAssetDepreciated`, `FixedAssetDisposed`, `FixedAssetDeleted`) after
 * the commit. Bookings go through the `Accounting\Journal` contract.
 */
interface FixedAssets
{
    public function find(int $id): ?FixedAssetData;

    /**
     * Assets ordered by inventory number (assets without one last, then by
     * id). `$page` starts at 1.
     */
    public function list(FixedAssetFilter $filter = new FixedAssetFilter, int $page = 1, int $perPage = 50): FixedAssetPage;

    /**
     * Assets adopted from an incoming invoice, ordered by id.
     *
     * @return list<FixedAssetData>
     */
    public function forIncomingInvoice(int $incomingInvoiceId): array;

    /**
     * The fixed-asset schedule (Anlagespiegel) of a calendar year.
     */
    public function scheduleForYear(int $year): FixedAssetSchedule;

    /**
     * The depreciation plan of one asset, year by year; empty for assets
     * without a plan (`ListOnly`).
     *
     * @return list<DepreciationPlanRow>
     *
     * @throws FixedAssetNotFoundException
     */
    public function depreciationPlan(int $id): array;

    /**
     * The depreciation of a fiscal year that `runDepreciation()` would book.
     */
    public function depreciationPreview(int $year): DepreciationPreview;

    /**
     * Adds a booked incoming-invoice line to the inventory list (`ListOnly`,
     * no inventory number, no accounting effect).
     *
     * @throws ContractException if the invoice is not booked, the line is linked to an article, has no positive
     *                           net amount or already has an asset, or the invoice carries legacy assets
     *                           without a line assignment.
     */
    public function create(NewFixedAsset $asset, ActorRef $actor): FixedAssetData;

    /**
     * Marks a not yet activated accounting asset as capitalized on a 0xxx
     * account. The capitalization posting itself is NOT made here — it is the
     * incoming invoice's booking; this call records that the cost sits on the
     * account so that the depreciation run takes the asset over.
     *
     * @throws FixedAssetNotFoundException
     * @throws ContractException if the asset is disposed, has no accounting effect, is already activated, or the
     *                           account is not an active 0xxx asset account.
     */
    public function activate(int $id, string $ledgerAccountNumber, ActorRef $actor): FixedAssetData;

    /**
     * @throws FixedAssetNotFoundException
     * @throws ContractException if a field that is fixed from the first posting on changes, or the kind switches
     *                           between `ListOnly` and an accounting kind.
     */
    public function update(int $id, FixedAssetUpdate $update, ActorRef $actor): FixedAssetData;

    /**
     * Books the open annual depreciation of all activated assets for a fiscal
     * year on the fiscal year's last day: debit the depreciation expense
     * account, credit the asset account. Idempotent per asset and year.
     *
     * @throws ContractException if the period of the booking date is already closed.
     */
    public function runDepreciation(int $year, ActorRef $actor): DepreciationRun;

    /**
     * Disposal without proceeds: books the pro-rata depreciation up to the
     * disposal date and writes off the remaining book value. Assets that were
     * never activated only change their register status.
     *
     * @throws FixedAssetNotFoundException
     * @throws ContractException if the asset is already disposed, is an inventory-list asset, the date precedes
     *                           the acquisition, or the annual depreciation of the disposal year is already booked.
     */
    public function dispose(int $id, string $disposedOn, ?string $note, ActorRef $actor): FixedAssetData;

    /**
     * Removes an asset that has no associated posting from the register.
     *
     * @throws FixedAssetNotFoundException
     * @throws ContractException if the asset is journal-linked (it leaves only through disposal or the
     *                           cancellation of its source document).
     */
    public function delete(int $id, ActorRef $actor): void;
}
