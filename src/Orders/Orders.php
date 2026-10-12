<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;

/**
 * The core's orders (Aufträge), complete for their domain: reads with filters
 * and every write the order service allows. The lifecycle is
 * draft → open (confirmed, number drawn) → completed or cancelled; only a
 * draft is editable.
 *
 * Rule violations (a status that does not allow the action, invalid input, a
 * tax treatment that needs confirmation, an active invoice on an order to
 * cancel, a veto of a module's `OrderHook`) surface as `ContractException`
 * with a German message; an unknown ID as `OrderNotFoundException`. The
 * actions run through the order service, so the `OrderHook`s of the enabled
 * modules fire inside the same transaction, and every mutation fires its
 * domain event after the commit: `OrderCreated` (`create()`,
 * `createFromQuote()`), `OrderUpdated`, `OrderConfirmed`, `OrderCompleted`,
 * `OrderCancelled`.
 */
interface Orders
{
    /**
     * The order with its lines, or null for an unknown ID.
     */
    public function find(int $id): ?OrderRef;

    /**
     * The order with the given order number (for example `AB-2026-00042`), or
     * null for an unknown number. Drafts have no number.
     */
    public function findByNumber(string $number): ?OrderRef;

    /**
     * Orders, newest first (by order date, then by ID); the references carry no
     * lines. `$page` starts at 1.
     */
    public function list(OrderFilter $filter = new OrderFilter, int $page = 1, int $perPage = 25): OrderPage;

    /**
     * One order line, or null for an unknown ID.
     */
    public function findItem(int $orderItemId): ?OrderItemRef;

    /**
     * Creates a standalone order DRAFT (no number yet).
     *
     * @throws ContractException if the input is invalid (no contact or billing address, no lines,
     *                           malformed amounts, unknown tax category or contact, ...).
     */
    public function create(NewOrder $order, ActorRef $actor): OrderRef;

    /**
     * Converts a quote into an order: the order is created OPEN (confirmed, numbered, immutable)
     * at once and starts a subscription per period-priced line.
     *
     * @throws ContractException if the quote does not exist or its status does not allow the conversion.
     */
    public function createFromQuote(int $quoteId, ActorRef $actor): OrderRef;

    /**
     * Replaces the content of a DRAFT.
     *
     * @throws OrderNotFoundException
     * @throws ContractException if the order is no longer a draft or the input is invalid.
     */
    public function update(int $id, OrderChanges $changes, ActorRef $actor): OrderRef;

    /**
     * Confirms a draft: draws the order number, sets the status to `open`, starts a subscription per
     * period-priced line and runs `OrderHook::afterConfirmed()` (reservation). An order with a special
     * or manually deviating tax treatment is only confirmed with `$taxReviewConfirmed`.
     *
     * @throws OrderNotFoundException
     * @throws ContractException if the order is not a draft, the tax treatment needs the confirmation,
     *                           or a hook refuses.
     */
    public function confirm(int $id, ActorRef $actor, bool $taxReviewConfirmed = false): OrderRef;

    /**
     * Completes an open order (delivery/sale point in time): `OrderHook::afterCompleted()` moves the
     * goods. The remarks of the hooks come back in `OrderRef::$warnings`; they never block.
     *
     * @throws OrderNotFoundException
     * @throws ContractException if the order is not open or a hook refuses.
     */
    public function complete(int $id, ActorRef $actor): OrderRef;

    /**
     * Cancels an open order: nothing is deleted, the hooks release or consume the material per
     * `OrderCancellation`, the subscriptions that were not billed yet end.
     *
     * @throws OrderNotFoundException
     * @throws ContractException if the order is not cancellable (draft, completed, already cancelled),
     *                           an active invoice exists (cancel it first), the cancellation is invalid
     *                           or a hook refuses.
     */
    public function cancel(int $id, OrderCancellation $cancellation, ActorRef $actor): OrderRef;
}
