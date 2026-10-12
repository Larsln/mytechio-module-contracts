<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\FixedAssets\DepreciationEntryData;
use MyTechIO\Contracts\FixedAssets\DepreciationPlanRow;
use MyTechIO\Contracts\FixedAssets\DepreciationPreview;
use MyTechIO\Contracts\FixedAssets\DepreciationPreviewRow;
use MyTechIO\Contracts\FixedAssets\DepreciationRun;
use MyTechIO\Contracts\FixedAssets\FixedAssetData;
use MyTechIO\Contracts\FixedAssets\FixedAssetFilter;
use MyTechIO\Contracts\FixedAssets\FixedAssetKind;
use MyTechIO\Contracts\FixedAssets\FixedAssetNotFoundException;
use MyTechIO\Contracts\FixedAssets\FixedAssetPage;
use MyTechIO\Contracts\FixedAssets\FixedAssetSchedule;
use MyTechIO\Contracts\FixedAssets\FixedAssetScheduleRow;
use MyTechIO\Contracts\FixedAssets\FixedAssetStatus;
use MyTechIO\Contracts\FixedAssets\FixedAssetUpdate;
use MyTechIO\Contracts\FixedAssets\NewFixedAsset;

function sampleFixedAsset(array $overrides = []): FixedAssetData
{
    return new FixedAssetData(...[
        'id' => 7,
        'inventoryNumber' => 'INV-2026-00001',
        'name' => 'Workstation',
        'kind' => FixedAssetKind::Depreciable,
        'category' => 'IT',
        'acquisitionDate' => '2026-02-15',
        'acquisitionCost' => '2500.0000',
        'usefulLifeYears' => 3,
        'depreciationStart' => '2026-02-15',
        'salvageValue' => '0.0000',
        'status' => FixedAssetStatus::Active,
        'disposedOn' => null,
        'disposalNote' => null,
        'ledgerAccountNumber' => '0650',
        'incomingInvoiceId' => 9,
        'incomingInvoiceItemId' => 31,
        'isActivated' => true,
        'isJournalLinked' => true,
        'notes' => null,
        'bookValue' => '2000.0000',
        'accumulatedDepreciation' => '500.0000',
        'createdBy' => 1,
        'createdAt' => '2026-03-01T10:00:00+00:00',
        'depreciationEntries' => [new DepreciationEntryData(7, 2026, '500.0000', 'annual', '12')],
        ...$overrides,
    ]);
}

it('knows which kinds have an accounting effect and need a useful life', function () {
    expect(FixedAssetKind::ListOnly->hasAccountingEffect())->toBeFalse()
        ->and(FixedAssetKind::Pool->hasAccountingEffect())->toBeTrue()
        ->and(FixedAssetKind::Depreciable->requiresUsefulLife())->toBeTrue()
        ->and(FixedAssetKind::Degressive->requiresUsefulLife())->toBeTrue()
        ->and(FixedAssetKind::LowValue->requiresUsefulLife())->toBeFalse()
        ->and(FixedAssetKind::from('non_depreciable'))->toBe(FixedAssetKind::NonDepreciable)
        ->and(FixedAssetStatus::from('disposed'))->toBe(FixedAssetStatus::Disposed);
});

it('converts an asset including its depreciation entries to a snake_case array', function () {
    $array = sampleFixedAsset()->toArray();

    expect($array['id'])->toBe(7)
        ->and($array['kind'])->toBe('depreciable')
        ->and($array['status'])->toBe('active')
        ->and($array['acquisition_cost'])->toBe('2500.0000')
        ->and($array['ledger_account_number'])->toBe('0650')
        ->and($array['is_activated'])->toBeTrue()
        ->and($array['is_journal_linked'])->toBeTrue()
        ->and($array['depreciation_entries'])->toBe([
            ['asset_id' => 7, 'fiscal_year' => 2026, 'amount' => '500.0000', 'kind' => 'annual', 'journal_entry_id' => '12'],
        ]);
});

it('converts the filter, update and creation inputs to arrays', function () {
    expect((new FixedAssetFilter)->toArray())->toBe([
        'kind' => null, 'include_list_only' => false, 'status' => null, 'year' => null, 'account_number' => null, 'search' => null,
    ])
        ->and((new FixedAssetFilter(FixedAssetKind::Pool, true, FixedAssetStatus::Active, 2026, '0675', 'Tisch'))->toArray()['kind'])->toBe('pool')
        ->and((new NewFixedAsset(31, 'IT', 'Notiz'))->toArray())->toBe(['incoming_invoice_item_id' => 31, 'category' => 'IT', 'notes' => 'Notiz'])
        ->and((new FixedAssetUpdate('Server', FixedAssetKind::Degressive, '2026-01-01', '900.0000', usefulLifeYears: 4))->toArray())->toMatchArray([
            'name' => 'Server', 'kind' => 'degressive', 'useful_life_years' => 4, 'depreciation_start' => null, 'salvage_value' => null,
        ]);
});

it('converts plan, preview, run and schedule shapes to arrays', function () {
    $row = new DepreciationPreviewRow(7, 'INV-1', 'Workstation', '0650', '6220', '500.0000');
    $entry = new DepreciationEntryData(7, 2026, '500.0000', 'annual', null);
    $scheduleRow = new FixedAssetScheduleRow(7, 'INV-1', 'Workstation', FixedAssetKind::Depreciable, '0650', 'Büroeinrichtung', true, '2500.0000', '2500.0000', null, '0.0000', '500.0000', '500.0000', '0.0000', '2000.0000');

    expect((new DepreciationPlanRow(2026, '2500.0000', '500.0000', '2000.0000'))->toArray())->toBe(['year' => 2026, 'opening' => '2500.0000', 'depreciation' => '500.0000', 'closing' => '2000.0000'])
        ->and((new DepreciationPreview(2026, [$row], '500.0000'))->toArray()['rows'][0]['expense_account_number'])->toBe('6220')
        ->and((new DepreciationRun(2026, 1, '500.0000', [$entry]))->toArray())->toBe([
            'year' => 2026, 'booked' => 1, 'total' => '500.0000', 'entries' => [$entry->toArray()],
        ])
        ->and((new FixedAssetSchedule(2026, [$scheduleRow], ['cost' => '2500.0000']))->toArray()['rows'][0])->toMatchArray([
            'asset_id' => 7, 'kind' => 'depreciable', 'addition' => '2500.0000', 'disposal' => null, 'book_end' => '2000.0000',
        ]);
});

it('converts a page including its items', function () {
    $page = new FixedAssetPage([sampleFixedAsset()], 41, 2, 20, 3);

    expect($page->toArray())->toMatchArray(['total' => 41, 'page' => 2, 'per_page' => 20, 'last_page' => 3])
        ->and($page->toArray()['items'][0]['name'])->toBe('Workstation');
});

it('carries the unknown id in the not-found exception, which is a contract exception', function () {
    $exception = new FixedAssetNotFoundException(99);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->id)->toBe(99)
        ->and($exception->getMessage())->toBe('Anlagegut mit ID 99 wurde nicht gefunden.');
});
