<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use DateTimeInterface;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Stock\CogsRow;
use MyTechIO\Contracts\Stock\NewInventoryCount;
use MyTechIO\Contracts\Stock\NewStockIssue;
use MyTechIO\Contracts\Stock\NewStockReceipt;
use MyTechIO\Contracts\Stock\NewWriteOff;
use MyTechIO\Contracts\Stock\Stock;
use MyTechIO\Contracts\Stock\StockArticleNotFoundException;
use MyTechIO\Contracts\Stock\StockIssueType;
use MyTechIO\Contracts\Stock\StockLevel;
use MyTechIO\Contracts\Stock\StockMovementData;
use MyTechIO\Contracts\Stock\StockMovementFilter;
use MyTechIO\Contracts\Stock\StockMovementPage;
use MyTechIO\Contracts\Stock\StockMovementType;
use MyTechIO\Contracts\Stock\StockReference;
use MyTechIO\Contracts\Stock\StockSettings;
use MyTechIO\Contracts\Stock\StockVoucherData;
use MyTechIO\Contracts\Stock\StockVoucherFilter;
use MyTechIO\Contracts\Stock\StockVoucherNotFoundException;
use MyTechIO\Contracts\Stock\StockVoucherPage;
use MyTechIO\Contracts\Stock\StockVoucherType;

/**
 * Test double for `Stock`: an in-memory quantity ledger with the core's rules
 * (positive quantities, tracked articles only, no negative stock on
 * write-offs, gapless `LB-<year>-<n>` voucher numbers, one counter-voucher per
 * reversal) but without any booking. `seedArticle()` makes an article known
 * (an unknown article raises `StockArticleNotFoundException`), its
 * `$onHand` is the opening stock. A fixed clock keeps results deterministic:
 * `today()` sets the date used when a call carries none. `events()` lists the
 * domain events the real implementation would have fired, as
 * `['event' => name, 'payload' => array]`.
 */
final class FakeStock implements Stock
{
    /**
     * @var array<int, array{tracked: bool, opening: int, reserved: int, reorderPoint: ?string, warning: bool}>
     */
    private array $articles = [];

    /**
     * @var list<StockMovementData>
     */
    private array $movements = [];

    /**
     * @var array<string, array{id: int, type: StockVoucherType, date: string, reason: string, reverses: ?string, createdBy: ?int, createdAt: string}>
     */
    private array $vouchers = [];

    /**
     * @var array<string, list<int>>
     */
    private array $voucherMovements = [];

    /**
     * @var array<string, string>
     */
    private array $reversedBy = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    /**
     * @var array<string, array{int, int}> articleId|month => [quantity, cost] in 1/10000 units
     */
    private array $cogs = [];

    private int $nextMovementId = 1;

    private int $nextVoucherId = 1;

    /**
     * @var array<int, int>
     */
    private array $voucherSequence = [];

    private string $today = '2026-01-15';

    public function today(string $date): void
    {
        $this->today = $date;
    }

    public function seedArticle(
        int $articleId,
        bool $tracked = true,
        string $onHand = '0.0000',
        ?string $reorderPoint = null,
        bool $lowStockWarningEnabled = false,
        string $reserved = '0.0000',
    ): void {
        $this->articles[$articleId] = [
            'tracked' => $tracked,
            'opening' => $this->units($onHand),
            'reserved' => $this->units($reserved),
            'reorderPoint' => $reorderPoint,
            'warning' => $lowStockWarningEnabled,
        ];
    }

    /**
     * Sets what `costOfGoodsSold()` reports for an article in a month.
     */
    public function seedCostOfGoodsSold(int $articleId, string $month, string $quantity, string $cogs): void
    {
        $this->cogs["{$articleId}|{$month}"] = [$this->units($quantity), $this->units($cogs)];
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    public function level(int $articleId): StockLevel
    {
        $this->known($articleId);

        return $this->levelOf($articleId);
    }

    public function levels(array $articleIds): array
    {
        $levels = [];

        foreach ($articleIds as $articleId) {
            if (isset($this->articles[$articleId])) {
                $levels[$articleId] = $this->levelOf($articleId);
            }
        }

        return $levels;
    }

    public function lowStock(): array
    {
        $ids = array_keys($this->articles);
        sort($ids);

        return array_values(array_filter(
            array_map(fn (int $id): StockLevel => $this->levelOf($id), $ids),
            static fn (StockLevel $level): bool => $level->isLow,
        ));
    }

    public function movements(StockMovementFilter $filter = new StockMovementFilter, int $page = 1, int $perPage = 50): StockMovementPage
    {
        $matches = array_values(array_filter(
            array_reverse($this->movements),
            fn (StockMovementData $movement): bool => $this->movementMatches($movement, $filter),
        ));
        $perPage = max(1, $perPage);
        $page = max(1, $page);

        return new StockMovementPage(
            items: array_slice($matches, ($page - 1) * $perPage, $perPage),
            total: count($matches),
            page: $page,
            perPage: $perPage,
            lastPage: max(1, (int) ceil(count($matches) / $perPage)),
        );
    }

    public function receipts(int $articleId, ?DateTimeInterface $since = null): array
    {
        $reversed = [];

        foreach ($this->movements as $movement) {
            if ($movement->type === StockMovementType::Correction && $this->units($movement->quantity) < 0 && $movement->referenceId !== null) {
                $reversed["{$movement->articleId}|{$movement->referenceType}|{$movement->referenceId}"] = true;
            }
        }

        $receipts = array_filter($this->movements, function (StockMovementData $movement) use ($articleId, $since, $reversed): bool {
            return $movement->articleId === $articleId
                && $movement->type === StockMovementType::Incoming
                && $this->units($movement->quantity) > 0
                && $movement->unitCostAtMovement !== null
                && $this->units($movement->unitCostAtMovement) > 0
                && ! isset($reversed["{$movement->articleId}|{$movement->referenceType}|{$movement->referenceId}"])
                && ($since === null || substr($movement->occurredAt, 0, 10) >= $since->format('Y-m-d'));
        });

        return array_values(array_reverse($receipts));
    }

    public function vouchers(StockVoucherFilter $filter = new StockVoucherFilter, int $page = 1, int $perPage = 25): StockVoucherPage
    {
        $matches = [];

        foreach (array_reverse(array_keys($this->vouchers)) as $number) {
            $voucher = $this->vouchers[$number];

            if ($filter->type !== null && $voucher['type'] !== $filter->type) {
                continue;
            }

            if (($filter->from !== null && $voucher['date'] < $filter->from) || ($filter->to !== null && $voucher['date'] > $filter->to)) {
                continue;
            }

            if ($filter->articleId !== null && ! $this->voucherHasArticle($number, $filter->articleId)) {
                continue;
            }

            $matches[] = $this->voucherData($number, withMovements: false);
        }

        $perPage = max(1, $perPage);
        $page = max(1, $page);

        return new StockVoucherPage(
            items: array_slice($matches, ($page - 1) * $perPage, $perPage),
            total: count($matches),
            page: $page,
            perPage: $perPage,
            lastPage: max(1, (int) ceil(count($matches) / $perPage)),
        );
    }

    public function findVoucher(string $number): ?StockVoucherData
    {
        return isset($this->vouchers[$number]) ? $this->voucherData($number) : null;
    }

    public function costOfGoodsSold(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $rows = [];

        foreach ($this->cogs as $key => [$quantity, $cogs]) {
            [$articleId, $month] = explode('|', $key);

            if ($month < $from->format('Y-m') || $month > $to->format('Y-m')) {
                continue;
            }

            $rows[] = new CogsRow((int) $articleId, $month, $this->format($quantity), $this->format($cogs));
        }

        usort($rows, static fn (CogsRow $a, CogsRow $b): int => [$a->month, $a->articleId] <=> [$b->month, $b->articleId]);

        return $rows;
    }

    public function recordReceipt(NewStockReceipt $receipt, ActorRef $actor): StockMovementData
    {
        $this->assertTracked($receipt->articleId);
        $this->assertPositive($receipt->quantity);

        $base = $receipt->unitCostBase;
        $total = $base === null ? null : $this->format($this->units($base) + $this->units($receipt->unitIncidentalCost ?? '0'));

        return $this->record(
            $receipt->articleId,
            StockMovementType::Incoming,
            $receipt->quantity,
            $actor,
            $receipt->occurredOn,
            $receipt->reason,
            $receipt->reference,
            null,
            $total,
            $base,
            $receipt->unitIncidentalCost,
        );
    }

    public function recordIssue(NewStockIssue $issue, ActorRef $actor): StockMovementData
    {
        $this->assertTracked($issue->articleId);
        $this->assertPositive($issue->quantity);

        $isSale = $issue->type === StockIssueType::Sale;

        return $this->record(
            $issue->articleId,
            $isSale ? StockMovementType::OutgoingSale : StockMovementType::Return,
            $isSale ? '-'.ltrim($issue->quantity, '-') : ltrim($issue->quantity, '-'),
            $actor,
            $issue->occurredOn,
            $issue->reason,
            $issue->reference,
            null,
            $issue->unitCost,
            $issue->unitCost,
            null,
        );
    }

    public function reserve(int $articleId, string $quantity, StockReference $reference): StockLevel
    {
        $this->assertTracked($articleId);
        $this->assertPositive($quantity);

        $this->articles[$articleId]['reserved'] += $this->units($quantity);

        return $this->levelOf($articleId);
    }

    public function release(int $articleId, string $quantity, StockReference $reference): StockLevel
    {
        $this->assertTracked($articleId);
        $this->assertPositive($quantity);

        $this->articles[$articleId]['reserved'] = max(0, $this->articles[$articleId]['reserved'] - $this->units($quantity));

        return $this->levelOf($articleId);
    }

    public function writeOff(NewWriteOff $writeOff, ActorRef $actor): StockVoucherData
    {
        if (! in_array($writeOff->type, StockMovementType::writeOffCases(), true)) {
            throw new ContractException('Die Bewegungsart ist kein Ausbuchungsgrund.');
        }

        $this->assertTracked($writeOff->articleId);
        $this->assertPositive($writeOff->quantity);
        $this->assertReason($writeOff->reason);

        $onHand = $this->onHand($writeOff->articleId);

        if ($this->units($writeOff->quantity) > $onHand) {
            throw new ContractException(sprintf(
                'Ausbuchen nicht möglich: Buchbestand %s, angefordert %s.',
                $this->format($onHand),
                $writeOff->quantity,
            ));
        }

        $date = $writeOff->voucherDate ?? $this->today;
        $number = $this->nextNumber($date);
        $this->createVoucher($number, StockVoucherType::WriteOff, $date, $writeOff->reason, null, $actor);
        $this->record($writeOff->articleId, $writeOff->type, '-'.ltrim($writeOff->quantity, '-'), $actor, $date, $writeOff->reason, null, $number);
        $this->events[] = ['event' => 'StockVoucherCreated', 'payload' => ['number' => $number, 'type' => 'write_off']];

        return $this->voucherData($number);
    }

    public function inventoryCount(NewInventoryCount $count, ActorRef $actor): ?StockVoucherData
    {
        $this->assertTracked($count->articleId);
        $this->assertReason($count->reason);

        if ($this->units($count->countedQuantity) < 0) {
            throw new ContractException('Die gezählte Menge darf nicht negativ sein.');
        }

        $difference = $this->units($count->countedQuantity) - $this->onHand($count->articleId);

        if ($difference === 0) {
            return null;
        }

        $date = $count->voucherDate ?? $this->today;
        $number = $this->nextNumber($date);
        $this->createVoucher($number, StockVoucherType::InventoryCount, $date, $count->reason, null, $actor);
        $this->record($count->articleId, StockMovementType::Correction, $this->format($difference), $actor, $date, $count->reason, null, $number);
        $this->events[] = ['event' => 'StockVoucherCreated', 'payload' => ['number' => $number, 'type' => 'inventory_count']];

        return $this->voucherData($number);
    }

    public function reverseVoucher(string $number, ActorRef $actor, string $reason): StockVoucherData
    {
        $voucher = $this->vouchers[$number] ?? throw new StockVoucherNotFoundException($number);
        $this->assertReason($reason);

        if ($voucher['reverses'] !== null) {
            throw new ContractException("Ein Storno-Beleg ({$number}) kann nicht erneut storniert werden.");
        }

        if (isset($this->reversedBy[$number])) {
            throw new ContractException("Der Beleg {$number} wurde bereits storniert.");
        }

        $counter = $this->nextNumber($this->today);
        $this->createVoucher($counter, $voucher['type'], $this->today, $reason, $number, $actor);
        $this->reversedBy[$number] = $counter;

        foreach ($this->voucherMovements[$number] as $movementId) {
            $original = $this->movementById($movementId);
            $this->record(
                $original->articleId,
                StockMovementType::Correction,
                $this->format(-$this->units($original->quantity)),
                $actor,
                $this->today,
                sprintf('Storno zu %s: %s', $number, $reason),
                null,
                $counter,
            );
        }

        $this->events[] = ['event' => 'StockVoucherReversed', 'payload' => ['number' => $number, 'reversed_by' => $counter]];

        return $this->voucherData($counter);
    }

    public function updateSettings(int $articleId, StockSettings $settings, ActorRef $actor): StockLevel
    {
        $this->known($articleId);

        if ($settings->lowStockWarningEnabled && $settings->reorderPoint === null) {
            throw new ContractException('Für die Bestandswarnung ist ein Meldebestand nötig.');
        }

        if ($settings->reorderPoint !== null && preg_match('/^\d+(\.\d{1,4})?$/', $settings->reorderPoint) !== 1) {
            throw new ContractException('Der Meldebestand muss eine nicht-negative Zahl sein.');
        }

        $this->articles[$articleId]['warning'] = $settings->lowStockWarningEnabled;
        $this->articles[$articleId]['reorderPoint'] = $settings->reorderPoint;

        if ($settings->tracked !== null) {
            $this->articles[$articleId]['tracked'] = $settings->tracked;
        }

        $this->events[] = ['event' => 'StockSettingsChanged', 'payload' => ['article_id' => $articleId]];

        return $this->levelOf($articleId);
    }

    private function record(
        int $articleId,
        StockMovementType $type,
        string $quantity,
        ActorRef $actor,
        ?string $date,
        ?string $reason,
        ?StockReference $reference,
        ?string $voucherNumber = null,
        ?string $unitCostAtMovement = null,
        ?string $unitCostBase = null,
        ?string $unitIncidentalCost = null,
    ): StockMovementData {
        $wasLow = $this->levelOf($articleId)->isLow;

        $movement = new StockMovementData(
            id: $this->nextMovementId++,
            articleId: $articleId,
            type: $type,
            quantity: $quantity,
            referenceType: $reference?->type,
            referenceId: $reference?->id,
            voucherNumber: $voucherNumber,
            reason: $reason,
            unitCostAtMovement: $unitCostAtMovement,
            unitCostBase: $unitCostBase,
            unitIncidentalCost: $unitIncidentalCost,
            bookedById: $actor->id,
            occurredAt: ($date ?? $this->today).'T00:00:00+00:00',
        );

        $this->movements[] = $movement;

        if ($voucherNumber !== null) {
            $this->voucherMovements[$voucherNumber][] = $movement->id;
        }

        $this->events[] = ['event' => 'StockMovementRecorded', 'payload' => ['movement_id' => $movement->id, 'article_id' => $articleId]];

        if (! $wasLow && $this->levelOf($articleId)->isLow) {
            $this->events[] = ['event' => 'LowStockDetected', 'payload' => ['article_id' => $articleId]];
        }

        return $movement;
    }

    private function createVoucher(string $number, StockVoucherType $type, string $date, string $reason, ?string $reverses, ActorRef $actor): void
    {
        $this->vouchers[$number] = [
            'id' => $this->nextVoucherId++,
            'type' => $type,
            'date' => $date,
            'reason' => $reason,
            'reverses' => $reverses,
            'createdBy' => $actor->id,
            'createdAt' => $this->today.'T00:00:00+00:00',
        ];
        $this->voucherMovements[$number] = [];
    }

    private function nextNumber(string $date): string
    {
        $year = (int) substr($date, 0, 4);
        $this->voucherSequence[$year] = ($this->voucherSequence[$year] ?? 0) + 1;

        return sprintf('LB-%d-%05d', $year, $this->voucherSequence[$year]);
    }

    private function voucherData(string $number, bool $withMovements = true): StockVoucherData
    {
        $voucher = $this->vouchers[$number];

        return new StockVoucherData(
            id: $voucher['id'],
            number: $number,
            type: $voucher['type'],
            voucherDate: $voucher['date'],
            reason: $voucher['reason'],
            bookingNote: null,
            ledgerEntryId: null,
            reversesNumber: $voucher['reverses'],
            reversedByNumber: $this->reversedBy[$number] ?? null,
            createdById: $voucher['createdBy'],
            createdAt: $voucher['createdAt'],
            movements: $withMovements
                ? array_map(fn (int $id): StockMovementData => $this->movementById($id), $this->voucherMovements[$number])
                : [],
        );
    }

    private function voucherHasArticle(string $number, int $articleId): bool
    {
        foreach ($this->voucherMovements[$number] as $movementId) {
            if ($this->movementById($movementId)->articleId === $articleId) {
                return true;
            }
        }

        return false;
    }

    private function movementById(int $id): StockMovementData
    {
        foreach ($this->movements as $movement) {
            if ($movement->id === $id) {
                return $movement;
            }
        }

        throw new ContractException("Bewegung {$id} existiert nicht.");
    }

    private function movementMatches(StockMovementData $movement, StockMovementFilter $filter): bool
    {
        $day = substr($movement->occurredAt, 0, 10);

        return ($filter->articleId === null || $movement->articleId === $filter->articleId)
            && ($filter->type === null || $movement->type === $filter->type)
            && ($filter->from === null || $day >= $filter->from)
            && ($filter->to === null || $day <= $filter->to)
            && ($filter->referenceType === null || $movement->referenceType === $filter->referenceType)
            && ($filter->referenceId === null || $movement->referenceId === $filter->referenceId)
            && ($filter->voucherNumber === null || $movement->voucherNumber === $filter->voucherNumber);
    }

    private function levelOf(int $articleId): StockLevel
    {
        $article = $this->articles[$articleId];

        if (! $article['tracked']) {
            return new StockLevel($articleId, false, '0.0000', '0.0000', '0.0000', $article['reorderPoint'], $article['warning'], false);
        }

        $onHand = $this->onHand($articleId);
        $available = $onHand - $article['reserved'];
        $isLow = $article['warning']
            && $article['reorderPoint'] !== null
            && $available <= $this->units($article['reorderPoint']);

        return new StockLevel(
            $articleId,
            true,
            $this->format($onHand),
            $this->format($article['reserved']),
            $this->format($available),
            $article['reorderPoint'],
            $article['warning'],
            $isLow,
        );
    }

    private function onHand(int $articleId): int
    {
        $sum = $this->articles[$articleId]['opening'];

        foreach ($this->movements as $movement) {
            if ($movement->articleId === $articleId) {
                $sum += $this->units($movement->quantity);
            }
        }

        return $sum;
    }

    private function known(int $articleId): void
    {
        if (! isset($this->articles[$articleId])) {
            throw new StockArticleNotFoundException($articleId);
        }
    }

    private function assertTracked(int $articleId): void
    {
        $this->known($articleId);

        if (! $this->articles[$articleId]['tracked']) {
            throw new ContractException('Der Artikel ist nicht lagergeführt.');
        }
    }

    private function assertPositive(string $quantity): void
    {
        if ($this->units($quantity) <= 0) {
            throw new ContractException('Die Menge muss größer als 0 sein.');
        }
    }

    private function assertReason(string $reason): void
    {
        if (trim($reason) === '') {
            throw new ContractException('Ein Grund ist erforderlich.');
        }
    }

    private function units(string $value): int
    {
        $negative = str_starts_with($value, '-');
        [$whole, $fraction] = array_pad(explode('.', ltrim($value, '-'), 2), 2, '');
        $units = (int) $whole * 10000 + (int) str_pad(substr($fraction, 0, 4), 4, '0');

        return $negative ? -$units : $units;
    }

    private function format(int $units): string
    {
        $sign = $units < 0 ? '-' : '';
        $digits = str_pad((string) abs($units), 5, '0', STR_PAD_LEFT);

        return $sign.substr($digits, 0, -4).'.'.substr($digits, -4);
    }
}
