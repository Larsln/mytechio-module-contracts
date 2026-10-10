<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\TaxClassification;

it('is a string-backed enum with the three tax classifications', function () {
    expect(TaxClassification::SupplyOfGoods->value)->toBe('supply_of_goods')
        ->and(TaxClassification::OtherService->value)->toBe('other_service')
        ->and(TaxClassification::ElectronicService->value)->toBe('electronic_service')
        ->and(TaxClassification::from('other_service'))->toBe(TaxClassification::OtherService)
        ->and(TaxClassification::cases())->toHaveCount(3);
});

it('provides German labels', function () {
    expect(TaxClassification::SupplyOfGoods->label())->toBe('Lieferung')
        ->and(TaxClassification::OtherService->label())->toBe('Sonstige Leistung')
        ->and(TaxClassification::ElectronicService->label())->toBe('Elektronisch erbrachte Leistung');
});
