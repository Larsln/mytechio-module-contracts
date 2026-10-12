<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeFixedAssets;
use MyTechIO\Contracts\FixedAssets\DepreciationPlanRow;
use MyTechIO\Contracts\FixedAssets\DepreciationPreview;
use MyTechIO\Contracts\FixedAssets\DepreciationPreviewRow;
use MyTechIO\Contracts\FixedAssets\FixedAssetData;
use MyTechIO\Contracts\FixedAssets\FixedAssetFilter;
use MyTechIO\Contracts\FixedAssets\FixedAssetKind;
use MyTechIO\Contracts\FixedAssets\FixedAssetNotFoundException;
use MyTechIO\Contracts\FixedAssets\FixedAssets;
use MyTechIO\Contracts\FixedAssets\FixedAssetSchedule;
use MyTechIO\Contracts\FixedAssets\FixedAssetStatus;
use MyTechIO\Contracts\FixedAssets\FixedAssetUpdate;
use MyTechIO\Contracts\FixedAssets\NewFixedAsset;

function seededAsset(int $id, array $overrides = []): FixedAssetData
{
    return new FixedAssetData(...[
        'id' => $id,
        'inventoryNumber' => sprintf('INV-2026-%05d', $id),
        'name' => "Asset {$id}",
        'kind' => FixedAssetKind::Depreciable,
        'category' => null,
        'acquisitionDate' => '2026-02-15',
        'acquisitionCost' => '1000.0000',
        'usefulLifeYears' => 3,
        'depreciationStart' => '2026-02-15',
        'salvageValue' => '0.0000',
        'status' => FixedAssetStatus::Active,
        'disposedOn' => null,
        'disposalNote' => null,
        'ledgerAccountNumber' => '0650',
        'incomingInvoiceId' => 9,
        'incomingInvoiceItemId' => 100 + $id,
        'isActivated' => true,
        'isJournalLinked' => true,
        'notes' => null,
        'bookValue' => '1000.0000',
        'accumulatedDepreciation' => '0.0000',
        ...$overrides,
    ]);
}

function fakeActor(): ActorRef
{
    return new ActorRef(7, 'Admin');
}

it('implements the FixedAssets contract and finds seeded assets', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(3));

    expect($assets)->toBeInstanceOf(FixedAssets::class)
        ->and($assets->find(3)?->name)->toBe('Asset 3')
        ->and($assets->find(99))->toBeNull();
});

it('lists ordered by inventory number, hides list-only by default and paginates', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(2));
    $assets->seed(seededAsset(1));
    $assets->seed(seededAsset(3, ['inventoryNumber' => null, 'kind' => FixedAssetKind::ListOnly, 'ledgerAccountNumber' => null, 'isActivated' => false, 'isJournalLinked' => false]));
    $assets->seed(seededAsset(4));

    $default = $assets->list();
    $all = $assets->list(new FixedAssetFilter(includeListOnly: true));
    $secondPage = $assets->list(new FixedAssetFilter(includeListOnly: true), page: 2, perPage: 3);

    expect(array_map(fn (FixedAssetData $a) => $a->id, $default->items))->toBe([1, 2, 4])
        ->and($default->total)->toBe(3)
        ->and(array_map(fn (FixedAssetData $a) => $a->id, $all->items))->toBe([1, 2, 4, 3])
        ->and(array_map(fn (FixedAssetData $a) => $a->id, $secondPage->items))->toBe([3])
        ->and($secondPage->page)->toBe(2)
        ->and($secondPage->lastPage)->toBe(2);
});

it('filters by kind, status, year, account and search', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1, ['name' => 'Schreibtisch']));
    $assets->seed(seededAsset(2, ['kind' => FixedAssetKind::Pool, 'ledgerAccountNumber' => '0675']));
    $assets->seed(seededAsset(3, ['status' => FixedAssetStatus::Disposed, 'disposedOn' => '2026-06-01']));
    $assets->seed(seededAsset(4, ['acquisitionDate' => '2027-01-10']));

    $ids = fn (FixedAssetFilter $filter): array => array_map(fn (FixedAssetData $a) => $a->id, $assets->list($filter)->items);

    expect($ids(new FixedAssetFilter(kind: FixedAssetKind::Pool)))->toBe([2])
        ->and($ids(new FixedAssetFilter(status: FixedAssetStatus::Disposed)))->toBe([3])
        ->and($ids(new FixedAssetFilter(accountNumber: '0675')))->toBe([2])
        ->and($ids(new FixedAssetFilter(search: 'schreib')))->toBe([1])
        ->and($ids(new FixedAssetFilter(year: 2026)))->toBe([1, 2, 3])
        ->and($ids(new FixedAssetFilter(year: 2027)))->toBe([1, 2, 4]);
});

it('returns the assets of an incoming invoice', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));
    $assets->seed(seededAsset(2, ['incomingInvoiceId' => 10]));

    expect(array_map(fn (FixedAssetData $a) => $a->id, $assets->forIncomingInvoice(9)))->toBe([1])
        ->and($assets->forIncomingInvoice(55))->toBe([]);
});

it('returns seeded schedules, plans and previews, empty ones otherwise', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));
    $assets->seedSchedule(new FixedAssetSchedule(2026, [], ['cost' => '1.0000']));
    $assets->seedPlan(1, [new DepreciationPlanRow(2026, '1000.0000', '250.0000', '750.0000')]);

    expect($assets->scheduleForYear(2026)->totals)->toBe(['cost' => '1.0000'])
        ->and($assets->scheduleForYear(2030)->rows)->toBe([])
        ->and($assets->depreciationPlan(1)[0]->depreciation)->toBe('250.0000')
        ->and($assets->depreciationPreview(2026)->total)->toBe('0.0000');
});

it('throws FixedAssetNotFoundException for the plan of an unknown asset', function () {
    (new FakeFixedAssets)->depreciationPlan(99);
})->throws(FixedAssetNotFoundException::class);

it('adds a booked line to the inventory list without an inventory number', function () {
    $assets = new FakeFixedAssets;
    $assets->seedLine(31, 9, 'Akkuschrauber', '2026-03-01', '89.0000');

    $created = $assets->create(new NewFixedAsset(31, 'Werkzeug'), fakeActor());

    expect($created->kind)->toBe(FixedAssetKind::ListOnly)
        ->and($created->inventoryNumber)->toBeNull()
        ->and($created->name)->toBe('Akkuschrauber')
        ->and($created->acquisitionCost)->toBe('89.0000')
        ->and($created->incomingInvoiceId)->toBe(9)
        ->and($created->isJournalLinked)->toBeFalse()
        ->and($assets->forIncomingInvoice(9))->toHaveCount(1)
        ->and($assets->events()[0]['event'])->toBe('FixedAssetCreated');
});

it('refuses creating from an unknown line, an article line or a second time', function () {
    $assets = new FakeFixedAssets;
    $assets->seedLine(31, 9, 'Akkuschrauber', '2026-03-01', '89.0000');
    $assets->seedLine(32, 9, 'Ware', '2026-03-01', '10.0000', hasArticle: true);
    $assets->create(new NewFixedAsset(31), fakeActor());

    $attempt = fn (int $itemId): ?string => (function () use ($assets, $itemId): ?string {
        try {
            $assets->create(new NewFixedAsset($itemId), fakeActor());
        } catch (ContractException $exception) {
            return $exception->getMessage();
        }

        return null;
    })();

    expect($attempt(999))->toBe('Die Position gehört zu keiner gebuchten Eingangsrechnung.')
        ->and($attempt(32))->toContain('Artikel verknüpft')
        ->and($attempt(31))->toBe('Für diese Position besteht bereits ein Anlagegut.');
});

it('activates an accounting asset once, on a 0xxx account', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1, ['isActivated' => false, 'ledgerAccountNumber' => null, 'isJournalLinked' => false]));
    $assets->seed(seededAsset(2, ['kind' => FixedAssetKind::ListOnly, 'isActivated' => false, 'ledgerAccountNumber' => null]));

    $activated = $assets->activate(1, '0650', fakeActor());

    expect($activated->isActivated)->toBeTrue()
        ->and($activated->ledgerAccountNumber)->toBe('0650')
        ->and($activated->isJournalLinked)->toBeTrue()
        ->and($assets->events()[0]['event'])->toBe('FixedAssetActivated')
        ->and(fn () => $assets->activate(1, '0650', fakeActor()))->toThrow(ContractException::class, 'bereits aktiviert')
        ->and(fn () => $assets->activate(2, '0650', fakeActor()))->toThrow(ContractException::class, 'Inventarliste')
        ->and(fn () => $assets->activate(99, '0650', fakeActor()))->toThrow(FixedAssetNotFoundException::class);
});

it('rejects activating on a non asset account', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1, ['isActivated' => false, 'ledgerAccountNumber' => null, 'isJournalLinked' => false]));

    $assets->activate(1, '6220', fakeActor());
})->throws(ContractException::class, '0xxx');

it('updates editable fields and locks booking-relevant ones from the first posting on', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));

    $renamed = $assets->update(1, new FixedAssetUpdate('Neuer Name', FixedAssetKind::Depreciable, '2026-02-15', '1000.0000', 'IT', 5, ledgerAccountNumber: '0650', notes: 'n'), fakeActor());

    expect($renamed->name)->toBe('Neuer Name')
        ->and($renamed->usefulLifeYears)->toBe(5)
        ->and($renamed->depreciationStart)->toBe('2026-02-15')
        ->and($renamed->salvageValue)->toBe('0.0000')
        ->and(fn () => $assets->update(1, new FixedAssetUpdate('x', FixedAssetKind::Depreciable, '2026-02-15', '999.0000', ledgerAccountNumber: '0650'), fakeActor()))
        ->toThrow(ContractException::class, 'fixiert');
});

it('allows editing everything of an asset without a posting but never switches list-only', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1, ['kind' => FixedAssetKind::ListOnly, 'ledgerAccountNumber' => null, 'isActivated' => false, 'isJournalLinked' => false]));

    $edited = $assets->update(1, new FixedAssetUpdate('Anders', FixedAssetKind::ListOnly, '2026-03-01', '5.0000'), fakeActor());

    expect($edited->acquisitionDate)->toBe('2026-03-01')
        ->and($edited->acquisitionCost)->toBe('5.0000')
        ->and(fn () => $assets->update(1, new FixedAssetUpdate('Anders', FixedAssetKind::Pool, '2026-03-01', '5.0000'), fakeActor()))
        ->toThrow(ContractException::class, 'Wechsel');
});

it('books the seeded preview once and records one event per posting', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));
    $assets->seedPreview(new DepreciationPreview(2026, [new DepreciationPreviewRow(1, 'INV-2026-00001', 'Asset 1', '0650', '6220', '250.0000')], '250.0000'));

    $first = $assets->runDepreciation(2026, fakeActor());
    $second = $assets->runDepreciation(2026, fakeActor());

    expect($first->booked)->toBe(1)
        ->and($first->total)->toBe('250.0000')
        ->and($first->entries[0]->kind)->toBe('annual')
        ->and($second->booked)->toBe(0)
        ->and($second->total)->toBe('0.0000')
        ->and($assets->depreciationPreview(2026)->rows)->toBe([])
        ->and($assets->find(1)->depreciationEntries)->toHaveCount(1)
        ->and($assets->events()[0]['event'])->toBe('FixedAssetDepreciated');
});

it('disposes an accounting asset once and rejects list-only assets and early dates', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));
    $assets->seed(seededAsset(2, ['kind' => FixedAssetKind::ListOnly, 'isJournalLinked' => false]));

    expect(fn () => $assets->dispose(1, '2026-01-01', null, fakeActor()))->toThrow(ContractException::class, 'vor dem Anschaffungsdatum')
        ->and(fn () => $assets->dispose(2, '2026-06-01', null, fakeActor()))->toThrow(ContractException::class, 'Inventarliste');

    $disposed = $assets->dispose(1, '2026-06-01', 'Defekt', fakeActor());

    expect($disposed->status)->toBe(FixedAssetStatus::Disposed)
        ->and($disposed->disposedOn)->toBe('2026-06-01')
        ->and($disposed->disposalNote)->toBe('Defekt')
        ->and($disposed->bookValue)->toBe('0.0000')
        ->and(fn () => $assets->dispose(1, '2026-07-01', null, fakeActor()))->toThrow(ContractException::class, 'bereits abgegangen');
});

it('deletes only assets without a posting', function () {
    $assets = new FakeFixedAssets;
    $assets->seed(seededAsset(1));
    $assets->seed(seededAsset(2, ['kind' => FixedAssetKind::ListOnly, 'isJournalLinked' => false]));

    expect(fn () => $assets->delete(1, fakeActor()))->toThrow(ContractException::class, 'Abgang erfassen');

    $assets->delete(2, fakeActor());

    expect($assets->find(2))->toBeNull()
        ->and($assets->events()[0]['event'])->toBe('FixedAssetDeleted')
        ->and(fn () => $assets->delete(2, fakeActor()))->toThrow(FixedAssetNotFoundException::class);
});
