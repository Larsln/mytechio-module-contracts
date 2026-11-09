<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeTaxCategories;
use MyTechIO\Contracts\Tax\TaxCategoryData;

function makeTaxCategory(int $id, string $rate = '19.00', bool $isActive = true): TaxCategoryData
{
    return new TaxCategoryData($id, "code-{$id}", "Category {$id}", $rate, $isActive, "{$rate} %");
}

it('returns seeded categories in insertion order and hides inactive ones by default', function () {
    $categories = new FakeTaxCategories;
    $categories->seed(makeTaxCategory(2, '7.00'));
    $categories->seed(makeTaxCategory(1));
    $categories->seed(makeTaxCategory(3, '0.00', false));

    expect(array_map(fn (TaxCategoryData $c) => $c->id, $categories->all()))->toBe([2, 1])
        ->and(array_map(fn (TaxCategoryData $c) => $c->id, $categories->all(true)))->toBe([2, 1, 3]);
});

it('finds a category by id including inactive ones', function () {
    $categories = new FakeTaxCategories;
    $categories->seed(makeTaxCategory(3, '0.00', false));

    expect($categories->find(3)?->isActive)->toBeFalse()
        ->and($categories->find(9))->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    expect(makeTaxCategory(1)->toArray())->toBe([
        'id' => 1,
        'code' => 'code-1',
        'name' => 'Category 1',
        'rate' => '19.00',
        'is_active' => true,
        'label' => '19.00 %',
    ]);
});

it('rejects a malformed rate', function () {
    makeTaxCategory(1, 'abc');
})->throws(ContractException::class);
