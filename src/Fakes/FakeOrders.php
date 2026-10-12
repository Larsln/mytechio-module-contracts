<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Orders\CancellationRef;
use MyTechIO\Contracts\Orders\NewOrder;
use MyTechIO\Contracts\Orders\NewOrderItem;
use MyTechIO\Contracts\Orders\OrderCancellation;
use MyTechIO\Contracts\Orders\OrderChanges;
use MyTechIO\Contracts\Orders\OrderFilter;
use MyTechIO\Contracts\Orders\OrderItemRef;
use MyTechIO\Contracts\Orders\OrderNotFoundException;
use MyTechIO\Contracts\Orders\OrderPage;
use MyTechIO\Contracts\Orders\OrderRef;
use MyTechIO\Contracts\Orders\Orders;

/**
 * Test double for `Orders`: in-memory orders with auto IDs and the core's
 * lifecycle rules (only a draft is editable and confirmable, only an open
 * order completes or cancels, the number `AB-<year>-<n>` is drawn on
 * confirmation, an active invoice blocks the cancellation, a cancellation
 * needs a reason of at least 10 characters).
 *
 * `seed()` sets the starting state, `seedQuote()` makes a quote available to
 * `createFromQuote()` (as the `NewOrder` it converts to), `seedContact()` names
 * a contact (and, once any contact is seeded, makes unknown contact IDs
 * invalid), `seedTaxRate()` sets the rate of a tax category (default `19.00`)
 * and `linkInvoice()` attaches an invoice to an order. `returnWarnings()` sets
 * the remarks `complete()` hands back. Totals are simplified: line amounts are
 * exact (banker's rounding), the order-wide discount is applied to net and tax
 * alike. A line with a manual `taxScenarioOverride` needs the tax-review
 * confirmation on `confirm()`. `events()` lists the domain events the real
 * implementation would have fired, as `['event' => name, 'payload' => array]`;
 * the actors of the writes are recorded in `actors()`. No `OrderHook` is run.
 */
final class FakeOrders implements Orders
{
    private const string RATE_DEFAULT = '19.00';

    /**
     * @var array<int, OrderRef>
     */
    private array $orders = [];

    /**
     * @var array<int, NewOrder>
     */
    private array $quotes = [];

    /**
     * @var array<int, true>
     */
    private array $convertedQuotes = [];

    /**
     * @var array<int, string>
     */
    private array $contacts = [];

    /**
     * @var array<int, string>
     */
    private array $taxRates = [];

    /**
     * @var array<int, array<int, bool>>
     */
    private array $invoices = [];

    /**
     * @var array<int, int>
     */
    private array $counters = [];

    /**
     * @var list<string>
     */
    private array $warnings = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    /**
     * @var list<array{action: string, id: int, actor: ActorRef}>
     */
    private array $actors = [];

    private int $nextId = 1;

    private int $nextItemId = 1;

    public function seed(OrderRef $order): void
    {
        $this->orders[$order->id] = $order;
        $this->nextId = max($this->nextId, $order->id + 1);

        foreach ($order->items as $item) {
            $this->nextItemId = max($this->nextItemId, $item->id + 1);
        }

        if ($order->number !== null && preg_match('/^.*?-(\d{4})-(\d+)$/', $order->number, $parts) === 1) {
            $this->counters[(int) $parts[1]] = max($this->counters[(int) $parts[1]] ?? 0, (int) $parts[2]);
        }
    }

    public function seedQuote(int $quoteId, NewOrder $quote): void
    {
        $this->quotes[$quoteId] = $quote;
    }

    public function seedContact(int $contactId, string $name): void
    {
        $this->contacts[$contactId] = $name;
    }

    public function seedTaxRate(int $taxCategoryId, string $rate): void
    {
        $this->taxRates[$taxCategoryId] = $rate;
    }

    /**
     * Attaches an invoice to an order; an `$active` invoice (not cancelled, no cancellation
     * document) blocks the cancellation of the order.
     */
    public function linkInvoice(int $orderId, int $invoiceId, bool $active = true): void
    {
        $order = $this->get($orderId);
        $this->invoices[$orderId][$invoiceId] = $active;
        $this->orders[$orderId] = $this->with($order, ['invoiceIds' => array_keys($this->invoices[$orderId])]);
    }

    /**
     * @param  list<string>  $warnings
     */
    public function returnWarnings(array $warnings): void
    {
        $this->warnings = $warnings;
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * @return list<array{action: string, id: int, actor: ActorRef}>
     */
    public function actors(): array
    {
        return $this->actors;
    }

    public function find(int $id): ?OrderRef
    {
        return $this->orders[$id] ?? null;
    }

    public function findByNumber(string $number): ?OrderRef
    {
        foreach ($this->orders as $order) {
            if ($order->number === $number) {
                return $order;
            }
        }

        return null;
    }

    public function list(OrderFilter $filter = new OrderFilter, int $page = 1, int $perPage = 25): OrderPage
    {
        $matches = array_values(array_filter($this->orders, fn (OrderRef $order): bool => $this->matches($order, $filter)));
        usort($matches, static fn (OrderRef $a, OrderRef $b): int => [$b->orderDate, $b->id] <=> [$a->orderDate, $a->id]);

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $slice = array_slice($matches, ($page - 1) * $perPage, $perPage);

        return new OrderPage(
            items: array_map(fn (OrderRef $order): OrderRef => $this->with($order, ['items' => []]), $slice),
            total: count($matches),
            page: $page,
            perPage: $perPage,
            lastPage: max(1, (int) ceil(count($matches) / $perPage)),
        );
    }

    public function findItem(int $orderItemId): ?OrderItemRef
    {
        foreach ($this->orders as $order) {
            foreach ($order->items as $item) {
                if ($item->id === $orderItemId) {
                    return $item;
                }
            }
        }

        return null;
    }

    public function create(NewOrder $order, ActorRef $actor): OrderRef
    {
        $this->validate($order);

        $id = $this->nextId++;
        $created = $this->with($this->build($id, $order, 'draft', null, null), ['createdBy' => $actor->id]);
        $this->orders[$id] = $created;
        $this->actors[] = ['action' => 'create', 'id' => $id, 'actor' => $actor];
        $this->record('OrderCreated', $created, ['quote_id' => null]);

        return $created;
    }

    public function createFromQuote(int $quoteId, ActorRef $actor): OrderRef
    {
        $quote = $this->quotes[$quoteId] ?? throw new ContractException(sprintf('Angebot mit ID %d wurde nicht gefunden.', $quoteId));

        if (isset($this->convertedQuotes[$quoteId])) {
            throw new ContractException('Angebot kann in diesem Status nicht in einen Auftrag umgewandelt werden.');
        }

        $this->convertedQuotes[$quoteId] = true;
        $id = $this->nextId++;
        $number = $this->drawNumber(substr($quote->orderDate, 0, 4));
        $created = $this->with($this->build($id, $quote, 'open', $number, $quoteId), ['createdBy' => $actor->id]);
        $this->orders[$id] = $created;
        $this->actors[] = ['action' => 'createFromQuote', 'id' => $id, 'actor' => $actor];
        $this->record('OrderCreated', $created, ['quote_id' => $quoteId]);

        return $created;
    }

    public function update(int $id, OrderChanges $changes, ActorRef $actor): OrderRef
    {
        $order = $this->get($id);

        if ($order->status !== 'draft') {
            throw new ContractException('Auftrag kann nicht mehr bearbeitet werden.');
        }

        $this->validate($changes);

        $updated = $this->build($id, $changes, 'draft', null, $order->quoteId, $order);
        $this->orders[$id] = $updated;
        $this->actors[] = ['action' => 'update', 'id' => $id, 'actor' => $actor];
        $this->record('OrderUpdated', $updated);

        return $updated;
    }

    public function confirm(int $id, ActorRef $actor, bool $taxReviewConfirmed = false): OrderRef
    {
        $order = $this->get($id);

        if ($order->status !== 'draft') {
            throw new ContractException('Der Auftrag kann in diesem Status nicht bestätigt werden.');
        }

        $needsReview = array_filter($order->items, static fn (OrderItemRef $item): bool => $item->taxScenarioOverride !== null) !== [];

        if ($needsReview && ! $taxReviewConfirmed) {
            throw new ContractException('Die Steuerbehandlung enthält Sonderfälle oder manuelle Abweichungen – sie muss ausdrücklich bestätigt werden, bevor der Auftrag bestätigt wird.');
        }

        $confirmed = $this->with($order, [
            'status' => 'open',
            'number' => $this->drawNumber(substr((string) $order->orderDate, 0, 4)),
        ]);
        $this->orders[$id] = $confirmed;
        $this->actors[] = ['action' => 'confirm', 'id' => $id, 'actor' => $actor];
        $this->record('OrderConfirmed', $confirmed);

        return $confirmed;
    }

    public function complete(int $id, ActorRef $actor): OrderRef
    {
        $order = $this->get($id);

        if ($order->status !== 'open') {
            throw new ContractException('Der Auftrag kann in diesem Status nicht abgeschlossen werden.');
        }

        $completed = $this->with($order, ['status' => 'completed']);
        $this->orders[$id] = $completed;
        $this->actors[] = ['action' => 'complete', 'id' => $id, 'actor' => $actor];
        $this->record('OrderCompleted', $completed);

        return $this->with($completed, ['warnings' => $this->warnings]);
    }

    public function cancel(int $id, OrderCancellation $cancellation, ActorRef $actor): OrderRef
    {
        $order = $this->get($id);

        if ($order->status === 'cancelled') {
            throw new ContractException('Der Auftrag ist bereits storniert.');
        }

        if (! in_array($order->status, ['open', 'partial'], true)) {
            throw new ContractException('Der Auftrag kann in diesem Status nicht storniert werden.');
        }

        $this->validateCancellation($order, $cancellation);

        if (in_array(true, $this->invoices[$id] ?? [], true)) {
            throw new ContractException('Zu diesem Auftrag existiert eine aktive Rechnung — bitte zuerst die Rechnung stornieren.');
        }

        $itemModes = $this->itemModes($order, $cancellation);
        $items = array_map(fn (OrderItemRef $item): OrderItemRef => $this->withItem($item, [
            'cancellationMode' => $itemModes[$item->id],
            'quantityReturned' => $itemModes[$item->id] === CancellationRef::FULL_RETURN ? $item->quantity : self::format(0),
            'quantityConsumed' => $itemModes[$item->id] === CancellationRef::CONSUMED ? $item->quantity : self::format(0),
        ]), $order->items);

        $cancelled = $this->with($order, [
            'status' => 'cancelled',
            'items' => $items,
            'cancellationMode' => $cancellation->mode,
            'cancellationReason' => $cancellation->reason,
            'cancelledBy' => $actor->id,
            'cancelledAt' => gmdate('c'),
        ]);
        $this->orders[$id] = $cancelled;
        $this->actors[] = ['action' => 'cancel', 'id' => $id, 'actor' => $actor];
        $this->record('OrderCancelled', $cancelled, ['reason' => $cancellation->reason, 'mode' => $cancellation->mode]);

        return $cancelled;
    }

    private function get(int $id): OrderRef
    {
        return $this->orders[$id] ?? throw new OrderNotFoundException($id);
    }

    private function matches(OrderRef $order, OrderFilter $filter): bool
    {
        if ($filter->status !== null && $order->status !== $filter->status) {
            return false;
        }

        if ($filter->contactId !== null && $order->contactId !== $filter->contactId) {
            return false;
        }

        if ($filter->from !== null && ($order->orderDate ?? '') < $filter->from) {
            return false;
        }

        if ($filter->to !== null && ($order->orderDate ?? '') > $filter->to) {
            return false;
        }

        if ($filter->hasOpenItems !== null && in_array($order->status, ['open', 'partial'], true) !== $filter->hasOpenItems) {
            return false;
        }

        if ($filter->search !== null && $filter->search !== '') {
            foreach ([$order->number, $order->projectTitle, $order->contactName] as $haystack) {
                if ($haystack !== null && mb_stripos($haystack, $filter->search) !== false) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    private function validate(NewOrder|OrderChanges $order): void
    {
        if ($order->contactId === null && $order->billingSnapshot === null) {
            throw new ContractException('Bitte einen Kontakt oder eine Rechnungsadresse angeben.');
        }

        if ($order->contactId !== null && $this->contacts !== [] && ! isset($this->contacts[$order->contactId])) {
            throw new ContractException(sprintf('Kontakt mit ID %d wurde nicht gefunden.', $order->contactId));
        }

        if ($order->items === []) {
            throw new ContractException('Ein Auftrag braucht mindestens eine Position.');
        }

        if ($order->requestedDeliveryDate !== null && $order->requestedDeliveryDate < $order->orderDate) {
            throw new ContractException('Das Wunschlieferdatum darf nicht vor dem Auftragsdatum liegen.');
        }

        foreach ($order->items as $position => $item) {
            $this->validateItem($item, $position + 1);
        }
    }

    private function validateItem(NewOrderItem $item, int $position): void
    {
        if (preg_match('/^\d+(\.\d{1,4})?$/', $item->quantity) !== 1 || self::parse($item->quantity) === 0) {
            throw new ContractException(sprintf('Position %d: Die Menge muss eine Zahl größer 0 sein.', $position));
        }

        if (preg_match('/^\d+(\.\d{1,4})?$/', $item->unitPriceNet) !== 1) {
            throw new ContractException(sprintf('Position %d: Der Einzelpreis ist ungültig.', $position));
        }

        if ($item->listPriceNet !== null && self::parse($item->discountPercent) > 0) {
            throw new ContractException(sprintf('Position %d: Festpreis und Rabatt %% schließen sich pro Position aus.', $position));
        }

        if ($item->taxScenarioOverride !== null && ($item->taxScenarioOverrideReason === null || $item->taxScenarioOverrideReason === '')) {
            throw new ContractException(sprintf('Position %d: Für ein abweichendes Steuerszenario ist eine Begründung nötig.', $position));
        }
    }

    private function validateCancellation(OrderRef $order, OrderCancellation $cancellation): void
    {
        if (! in_array($cancellation->mode, [CancellationRef::FULL_RETURN, CancellationRef::CONSUMED, CancellationRef::MIXED], true)) {
            throw new ContractException('Der Stornomodus ist ungültig.');
        }

        if (mb_strlen($cancellation->reason) < 10) {
            throw new ContractException('Die Begründung muss mindestens 10 Zeichen lang sein.');
        }

        if ($cancellation->mode !== CancellationRef::MIXED) {
            return;
        }

        $ids = array_map(static fn (OrderItemRef $item): int => $item->id, $order->items);

        foreach ($cancellation->itemModes as $itemId => $mode) {
            if (! in_array($itemId, $ids, true) || ! in_array($mode, [CancellationRef::FULL_RETURN, CancellationRef::CONSUMED], true)) {
                throw new ContractException('Die Angaben je Position passen nicht zum Auftrag.');
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function itemModes(OrderRef $order, OrderCancellation $cancellation): array
    {
        $modes = [];

        foreach ($order->items as $item) {
            $modes[$item->id] = match ($cancellation->mode) {
                CancellationRef::FULL_RETURN => CancellationRef::FULL_RETURN,
                CancellationRef::CONSUMED => CancellationRef::CONSUMED,
                default => $cancellation->itemModes[$item->id] ?? CancellationRef::FULL_RETURN,
            };
        }

        return $modes;
    }

    private function drawNumber(string $year): string
    {
        $counter = ($this->counters[(int) $year] ?? 0) + 1;
        $this->counters[(int) $year] = $counter;

        return sprintf('AB-%s-%05d', $year, $counter);
    }

    private function build(int $id, NewOrder|OrderChanges $input, string $status, ?string $number, ?int $quoteId, ?OrderRef $previous = null): OrderRef
    {
        $subtotal = 0;
        $taxSum = 0;
        $items = [];

        foreach ($input->items as $position => $item) {
            $line = $this->buildItem($item, $position + 1);
            $subtotal += self::parse($line->netAmount);
            $taxSum += self::parse((string) $line->taxAmount);
            $items[] = $line;
        }

        $discountFactor = 1000000 - self::parse($input->customerDiscountPercent);
        $netTotal = self::divide($subtotal * $discountFactor, 1000000);
        $taxTotal = self::divide($taxSum * $discountFactor, 1000000);
        $createdAt = $previous->createdAt ?? gmdate('c');

        return new OrderRef(
            id: $id,
            number: $number,
            status: $status,
            contactId: $input->contactId,
            orderDate: $input->orderDate,
            quoteId: $quoteId,
            items: $items,
            url: "/orders/{$id}",
            contactName: $input->contactId !== null ? ($this->contacts[$input->contactId] ?? null) : self::snapshotName($input->billingSnapshot),
            requestedDeliveryDate: $input->requestedDeliveryDate,
            projectTitle: $input->projectTitle,
            introductionText: $input->introductionText,
            footerText: $input->footerText,
            internalNotes: $input->internalNotes,
            netSubtotal: self::format($subtotal),
            customerDiscountPercent: self::format(self::parse($input->customerDiscountPercent), 2),
            customerDiscountAmount: self::format($subtotal - $netTotal),
            netTotal: self::format($netTotal),
            taxTotal: self::format($taxTotal),
            grossTotal: self::format($netTotal + $taxTotal),
            grandTotal: self::format($netTotal + $taxTotal),
            vatMode: 'ist',
            taxScenario: 'de_domestic',
            taxCalculationMethod: ($input->billingSnapshot['type'] ?? null) === 'b2b' ? 'top_down' : 'line_level',
            paymentTermsDays: $input->paymentTermsDays,
            skontoPercent: $input->skontoPercent,
            skontoDays: $input->skontoDays,
            bankAccountId: $input->bankAccountId,
            billingSnapshot: $input->billingSnapshot,
            invoiceIds: $previous->invoiceIds ?? [],
            createdBy: $previous->createdBy ?? null,
            createdAt: $createdAt,
        );
    }

    private function buildItem(NewOrderItem $item, int $position): OrderItemRef
    {
        $rate = $this->taxRates[$item->taxCategoryId] ?? self::RATE_DEFAULT;
        $discount = $item->listPriceNet !== null ? 0 : self::parse($item->discountPercent);
        $gross = self::divide(self::parse($item->quantity) * self::parse($item->unitPriceNet), 10000);
        $net = self::divide($gross * (1000000 - $discount), 1000000);
        $tax = self::divide($net * self::parse($rate), 1000000);

        return new OrderItemRef(
            id: $this->nextItemId++,
            position: $position,
            articleId: $item->articleId,
            description: $item->description,
            quantity: self::format(self::parse($item->quantity)),
            unit: $item->unit,
            netAmount: self::format($net),
            articlePriceOptionId: $item->articlePriceOptionId,
            unitPriceNet: self::format(self::parse($item->unitPriceNet)),
            discountPercent: self::format($discount, 2),
            listPriceNet: $item->listPriceNet === null ? null : self::format(self::parse($item->listPriceNet)),
            showListPrice: $item->showListPrice && ($item->listPriceNet !== null || $discount > 0),
            taxCategoryId: $item->taxCategoryId,
            taxRate: self::format(self::parse($rate), 2),
            taxScenario: 'de_domestic',
            taxScenarioOverride: $item->taxScenarioOverride,
            taxScenarioOverrideReason: $item->taxScenarioOverrideReason,
            taxClassification: $item->taxClassification,
            serviceType: $item->serviceType,
            taxAmount: self::format($tax),
            grossAmount: self::format($net + $tax),
            revenueAccountId: $item->revenueAccountId,
            quantityDelivered: self::format(0),
            quantityReturned: self::format(0),
            quantityConsumed: self::format(0),
            groupId: $item->groupId,
            groupName: $item->groupName,
            groupCollapsed: $item->groupId === null ? null : ($item->groupCollapsed ?? true),
        );
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    private static function snapshotName(?array $snapshot): ?string
    {
        if ($snapshot === null) {
            return null;
        }

        $name = $snapshot['company_name'] ?? trim(($snapshot['first_name'] ?? '').' '.($snapshot['last_name'] ?? ''));

        return $name === '' ? null : (string) $name;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function record(string $event, OrderRef $order, array $payload = []): void
    {
        $base = ['order_id' => $order->id, 'number' => $order->number, 'contact_id' => $order->contactId];

        $this->events[] = ['event' => $event, 'payload' => match ($event) {
            'OrderCreated' => $base + ['quote_id' => $payload['quote_id']],
            'OrderCancelled' => $base + ['reason' => $payload['reason'], 'mode' => $payload['mode']],
            default => $base,
        }];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function with(OrderRef $order, array $overrides): OrderRef
    {
        return new OrderRef(...array_merge(get_object_vars($order), $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function withItem(OrderItemRef $item, array $overrides): OrderItemRef
    {
        return new OrderItemRef(...array_merge(get_object_vars($item), $overrides));
    }

    /**
     * A decimal string as an integer with 4 decimals.
     */
    private static function parse(string $value): int
    {
        $negative = str_starts_with($value, '-');
        [$whole, $fraction] = array_pad(explode('.', ltrim($value, '-'), 2), 2, '');
        $units = (int) $whole * 10000 + (int) str_pad(substr($fraction, 0, 4), 4, '0');

        return $negative ? -$units : $units;
    }

    private static function format(int $units, int $decimals = 4): string
    {
        $sign = $units < 0 ? '-' : '';
        $units = abs($units);
        $text = sprintf('%d.%04d', intdiv($units, 10000), $units % 10000);

        return $sign.($decimals === 4 ? $text : substr($text, 0, strlen($text) - (4 - $decimals)));
    }

    /**
     * Integer division, rounded half to even.
     */
    private static function divide(int $numerator, int $denominator): int
    {
        $negative = ($numerator < 0) !== ($denominator < 0);
        $numerator = abs($numerator);
        $denominator = abs($denominator);
        $quotient = intdiv($numerator, $denominator);
        $twiceRemainder = ($numerator % $denominator) * 2;

        if ($twiceRemainder > $denominator || ($twiceRemainder === $denominator && $quotient % 2 === 1)) {
            $quotient++;
        }

        return $negative ? -$quotient : $quotient;
    }
}
