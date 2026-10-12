<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\FixedAssets\DepreciationEntryData;
use MyTechIO\Contracts\FixedAssets\DepreciationPlanRow;
use MyTechIO\Contracts\FixedAssets\DepreciationPreview;
use MyTechIO\Contracts\FixedAssets\DepreciationRun;
use MyTechIO\Contracts\FixedAssets\FixedAssetData;
use MyTechIO\Contracts\FixedAssets\FixedAssetFilter;
use MyTechIO\Contracts\FixedAssets\FixedAssetKind;
use MyTechIO\Contracts\FixedAssets\FixedAssetNotFoundException;
use MyTechIO\Contracts\FixedAssets\FixedAssetPage;
use MyTechIO\Contracts\FixedAssets\FixedAssets;
use MyTechIO\Contracts\FixedAssets\FixedAssetSchedule;
use MyTechIO\Contracts\FixedAssets\FixedAssetStatus;
use MyTechIO\Contracts\FixedAssets\FixedAssetUpdate;
use MyTechIO\Contracts\FixedAssets\NewFixedAsset;

/**
 * Test double for `FixedAssets`: in-memory register with auto IDs and the
 * core's guards (locked fields, no disposal of inventory-list assets, no
 * deletion of journal-linked assets), but without any depreciation
 * arithmetic. `seed()` sets the starting state; `seedLine()` makes an
 * incoming-invoice line available to `create()`; `seedPreview()`,
 * `seedSchedule()` and `seedPlan()` set what the calculating reads return.
 * `runDepreciation()` books exactly the seeded preview of the year (once —
 * a second run books nothing). `events()` lists the domain events the real
 * implementation would have fired, as `['event' => name, 'payload' => array]`.
 */
final class FakeFixedAssets implements FixedAssets
{
    /**
     * @var array<int, FixedAssetData>
     */
    private array $assets = [];

    /**
     * @var array<int, array{incomingInvoiceId: int, name: string, acquisitionDate: string, acquisitionCost: string, hasArticle: bool}>
     */
    private array $lines = [];

    /**
     * @var array<int, DepreciationPreview>
     */
    private array $previews = [];

    /**
     * @var array<int, FixedAssetSchedule>
     */
    private array $schedules = [];

    /**
     * @var array<int, list<DepreciationPlanRow>>
     */
    private array $plans = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    private int $nextId = 1;

    public function seed(FixedAssetData $asset): void
    {
        $this->assets[$asset->id] = $asset;
        $this->nextId = max($this->nextId, $asset->id + 1);
    }

    public function seedLine(
        int $incomingInvoiceItemId,
        int $incomingInvoiceId,
        string $name,
        string $acquisitionDate,
        string $acquisitionCost,
        bool $hasArticle = false,
    ): void {
        $this->lines[$incomingInvoiceItemId] = [
            'incomingInvoiceId' => $incomingInvoiceId,
            'name' => $name,
            'acquisitionDate' => $acquisitionDate,
            'acquisitionCost' => $acquisitionCost,
            'hasArticle' => $hasArticle,
        ];
    }

    public function seedPreview(DepreciationPreview $preview): void
    {
        $this->previews[$preview->year] = $preview;
    }

    public function seedSchedule(FixedAssetSchedule $schedule): void
    {
        $this->schedules[$schedule->year] = $schedule;
    }

    /**
     * @param  list<DepreciationPlanRow>  $rows
     */
    public function seedPlan(int $assetId, array $rows): void
    {
        $this->plans[$assetId] = $rows;
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    public function find(int $id): ?FixedAssetData
    {
        return $this->assets[$id] ?? null;
    }

    public function list(FixedAssetFilter $filter = new FixedAssetFilter, int $page = 1, int $perPage = 50): FixedAssetPage
    {
        $matches = array_values(array_filter($this->sorted(), fn (FixedAssetData $asset): bool => $this->matches($asset, $filter)));
        $perPage = max(1, $perPage);
        $page = max(1, $page);

        return new FixedAssetPage(
            items: array_slice($matches, ($page - 1) * $perPage, $perPage),
            total: count($matches),
            page: $page,
            perPage: $perPage,
            lastPage: max(1, (int) ceil(count($matches) / $perPage)),
        );
    }

    public function forIncomingInvoice(int $incomingInvoiceId): array
    {
        return array_values(array_filter(
            $this->sorted(),
            static fn (FixedAssetData $asset): bool => $asset->incomingInvoiceId === $incomingInvoiceId,
        ));
    }

    public function scheduleForYear(int $year): FixedAssetSchedule
    {
        return $this->schedules[$year] ?? new FixedAssetSchedule($year, [], [
            'cost' => '0.0000',
            'additions' => '0.0000',
            'disposals' => '0.0000',
            'depreciation' => '0.0000',
            'book_start' => '0.0000',
            'book_end' => '0.0000',
        ]);
    }

    public function depreciationPlan(int $id): array
    {
        $this->get($id);

        return $this->plans[$id] ?? [];
    }

    public function depreciationPreview(int $year): DepreciationPreview
    {
        $preview = $this->previews[$year] ?? new DepreciationPreview($year, [], '0.0000');
        $done = $this->bookedAssetIds($year);

        $rows = array_values(array_filter(
            $preview->rows,
            static fn ($row): bool => ! in_array($row->assetId, $done, true),
        ));

        return new DepreciationPreview($year, $rows, $this->sum(array_map(static fn ($row): string => $row->amount, $rows)));
    }

    public function create(NewFixedAsset $asset, ActorRef $actor): FixedAssetData
    {
        $line = $this->lines[$asset->incomingInvoiceItemId]
            ?? throw new ContractException('Die Position gehört zu keiner gebuchten Eingangsrechnung.');

        if ($line['hasArticle']) {
            throw new ContractException('Diese Position ist mit einem Artikel verknüpft — keine Aufnahme ins Inventar.');
        }

        foreach ($this->assets as $existing) {
            if ($existing->incomingInvoiceItemId === $asset->incomingInvoiceItemId) {
                throw new ContractException('Für diese Position besteht bereits ein Anlagegut.');
            }
        }

        $id = $this->nextId++;
        $data = new FixedAssetData(
            id: $id,
            inventoryNumber: null,
            name: $line['name'],
            kind: FixedAssetKind::ListOnly,
            category: $asset->category,
            acquisitionDate: $line['acquisitionDate'],
            acquisitionCost: $line['acquisitionCost'],
            usefulLifeYears: null,
            depreciationStart: $line['acquisitionDate'],
            salvageValue: '0.0000',
            status: FixedAssetStatus::Active,
            disposedOn: null,
            disposalNote: null,
            ledgerAccountNumber: null,
            incomingInvoiceId: $line['incomingInvoiceId'],
            incomingInvoiceItemId: $asset->incomingInvoiceItemId,
            isActivated: false,
            isJournalLinked: false,
            notes: $asset->notes,
            bookValue: $line['acquisitionCost'],
            accumulatedDepreciation: '0.0000',
            createdBy: $actor->id,
        );

        $this->assets[$id] = $data;
        $this->record('FixedAssetCreated', ['asset_id' => $id, 'kind' => $data->kind->value]);

        return $data;
    }

    public function activate(int $id, string $ledgerAccountNumber, ActorRef $actor): FixedAssetData
    {
        $asset = $this->get($id);

        if ($asset->status === FixedAssetStatus::Disposed) {
            throw new ContractException('Das Anlagegut ist bereits abgegangen.');
        }

        if (! $asset->kind->hasAccountingEffect()) {
            throw new ContractException('„Nur Inventarliste“-Güter werden nicht aktiviert.');
        }

        if ($asset->isActivated) {
            throw new ContractException('Das Anlagegut ist bereits aktiviert.');
        }

        if (! preg_match('/^0\d{3}$/', $ledgerAccountNumber)) {
            throw new ContractException('Zum Aktivieren ist ein Anlagekonto (0xxx) nötig.');
        }

        $activated = $this->replace($asset, ['ledgerAccountNumber' => $ledgerAccountNumber, 'isActivated' => true, 'isJournalLinked' => true]);
        $this->record('FixedAssetActivated', ['asset_id' => $id, 'ledger_account_number' => $ledgerAccountNumber]);

        return $activated;
    }

    public function update(int $id, FixedAssetUpdate $update, ActorRef $actor): FixedAssetData
    {
        $asset = $this->get($id);

        if (($asset->kind === FixedAssetKind::ListOnly) !== ($update->kind === FixedAssetKind::ListOnly)) {
            throw new ContractException('Ein Wechsel zwischen „Nur Inventarliste“ und einer buchhalterischen Abschreibungsart ist nicht möglich.');
        }

        if ($asset->isJournalLinked && (
            $update->acquisitionCost !== $asset->acquisitionCost
            || $update->acquisitionDate !== $asset->acquisitionDate
            || $update->kind !== $asset->kind
            || ($update->ledgerAccountNumber ?? null) !== $asset->ledgerAccountNumber
        )) {
            throw new ContractException('Anschaffungskosten, -datum, Abschreibungsart und Anlagekonto sind ab der ersten Buchung fixiert — Korrektur nur über Storno des Herkunftsbelegs bzw. Abgang.');
        }

        return $this->replace($asset, [
            'name' => $update->name,
            'kind' => $update->kind,
            'category' => $update->category,
            'acquisitionDate' => $update->acquisitionDate,
            'acquisitionCost' => $update->acquisitionCost,
            'usefulLifeYears' => $update->usefulLifeYears,
            'depreciationStart' => $update->depreciationStart ?? $update->acquisitionDate,
            'salvageValue' => $update->salvageValue ?? '0.0000',
            'ledgerAccountNumber' => $update->kind === FixedAssetKind::ListOnly ? null : $update->ledgerAccountNumber,
            'notes' => $update->notes,
        ]);
    }

    public function runDepreciation(int $year, ActorRef $actor): DepreciationRun
    {
        $preview = $this->depreciationPreview($year);
        $entries = [];

        foreach ($preview->rows as $row) {
            $entry = new DepreciationEntryData($row->assetId, $year, $row->amount, 'annual', (string) (count($entries) + 1));
            $entries[] = $entry;

            $asset = $this->get($row->assetId);
            $this->replace($asset, [
                'isJournalLinked' => true,
                'depreciationEntries' => [...$asset->depreciationEntries, $entry],
            ]);
            $this->record('FixedAssetDepreciated', $entry->toArray());
        }

        return new DepreciationRun($year, count($entries), $preview->total, $entries);
    }

    public function dispose(int $id, string $disposedOn, ?string $note, ActorRef $actor): FixedAssetData
    {
        $asset = $this->get($id);

        if ($asset->status === FixedAssetStatus::Disposed) {
            throw new ContractException('Das Anlagegut ist bereits abgegangen.');
        }

        if (! $asset->kind->hasAccountingEffect()) {
            throw new ContractException('„Nur Inventarliste“-Güter kennen keinen Abgang — bitte löschen statt Abgang erfassen.');
        }

        if ($disposedOn < $asset->acquisitionDate) {
            throw new ContractException('Das Abgangsdatum liegt vor dem Anschaffungsdatum.');
        }

        $disposed = $this->replace($asset, [
            'status' => FixedAssetStatus::Disposed,
            'disposedOn' => $disposedOn,
            'disposalNote' => $note,
            'bookValue' => '0.0000',
        ]);
        $this->record('FixedAssetDisposed', ['asset_id' => $id, 'disposed_on' => $disposedOn]);

        return $disposed;
    }

    public function delete(int $id, ActorRef $actor): void
    {
        $asset = $this->get($id);

        if ($asset->isJournalLinked) {
            throw new ContractException('Dieses Anlagegut hängt an einer Buchung — bitte einen Abgang erfassen statt zu löschen.');
        }

        unset($this->assets[$id]);
        $this->record('FixedAssetDeleted', ['asset_id' => $id]);
    }

    /**
     * @return list<FixedAssetData>
     */
    private function sorted(): array
    {
        $assets = array_values($this->assets);

        usort($assets, static fn (FixedAssetData $a, FixedAssetData $b): int => [$a->inventoryNumber === null, $a->inventoryNumber, $a->id]
            <=> [$b->inventoryNumber === null, $b->inventoryNumber, $b->id]);

        return $assets;
    }

    private function matches(FixedAssetData $asset, FixedAssetFilter $filter): bool
    {
        if ($filter->kind !== null) {
            if ($asset->kind !== $filter->kind) {
                return false;
            }
        } elseif (! $filter->includeListOnly && $asset->kind === FixedAssetKind::ListOnly) {
            return false;
        }

        if ($filter->status !== null && $asset->status !== $filter->status) {
            return false;
        }

        if ($filter->accountNumber !== null && $asset->ledgerAccountNumber !== $filter->accountNumber) {
            return false;
        }

        if ($filter->year !== null && (
            (int) substr($asset->acquisitionDate, 0, 4) > $filter->year
            || ($asset->disposedOn !== null && (int) substr($asset->disposedOn, 0, 4) < $filter->year)
        )) {
            return false;
        }

        if ($filter->search !== null && $filter->search !== '') {
            $haystack = mb_strtolower(implode(' ', [$asset->name, $asset->inventoryNumber ?? '', $asset->category ?? '']));

            return str_contains($haystack, mb_strtolower($filter->search));
        }

        return true;
    }

    /**
     * @return list<int>
     */
    private function bookedAssetIds(int $year): array
    {
        $ids = [];

        foreach ($this->assets as $asset) {
            foreach ($asset->depreciationEntries as $entry) {
                if ($entry->fiscalYear === $year) {
                    $ids[] = $asset->id;
                }
            }
        }

        return $ids;
    }

    /**
     * Exact sum of amounts with 4 decimal places, via integer ten-thousandths.
     *
     * @param  list<string>  $amounts
     */
    private function sum(array $amounts): string
    {
        $units = 0;

        foreach ($amounts as $amount) {
            $units += (int) str_replace('.', '', $amount);
        }

        $sign = $units < 0 ? '-' : '';
        $digits = str_pad((string) abs($units), 5, '0', STR_PAD_LEFT);

        return $sign.substr($digits, 0, -4).'.'.substr($digits, -4);
    }

    private function get(int $id): FixedAssetData
    {
        return $this->assets[$id] ?? throw new FixedAssetNotFoundException($id);
    }

    /**
     * @param  array<string, mixed>  $changes  Constructor arguments of `FixedAssetData` to override.
     */
    private function replace(FixedAssetData $current, array $changes): FixedAssetData
    {
        $updated = new FixedAssetData(...[...get_object_vars($current), ...$changes]);

        $this->assets[$updated->id] = $updated;

        return $updated;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function record(string $event, array $payload): void
    {
        $this->events[] = ['event' => $event, 'payload' => $payload];
    }
}
