<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeShoppingNeeds;
use MyTechIO\Contracts\Purchasing\ShoppingNeed;
use MyTechIO\Contracts\Purchasing\ShoppingNeeds;

it('keeps what a source needs, sums an article named twice and replaces it all on the next statement', function () {
    $needs = new FakeShoppingNeeds;
    $needs->replace('shop', 'Webshop', [new ShoppingNeed(11, '15', 'für 2 Bestellungen'), new ShoppingNeed(9, '1'), new ShoppingNeed(11, '0.5')]);
    $needs->replace('other', 'Projekt', [new ShoppingNeed(11, '3')]);

    expect($needs)->toBeInstanceOf(ShoppingNeeds::class)
        ->and($needs->open('shop'))->toBe([11 => '15.500', 9 => '1.000'])
        ->and($needs->open('other'))->toBe([11 => '3.000'])
        ->and($needs->open('nobody'))->toBe([])
        ->and($needs->label('shop'))->toBe('Webshop')
        ->and($needs->note('shop', 9))->toBeNull()
        ->and($needs->calls())->toBe(2);

    $needs->replace('shop', 'Webshop', []);

    expect($needs->open('shop'))->toBe([])->and($needs->open('other'))->toBe([11 => '3.000']);
});

it('refuses a need without a positive quantity', function (string $quantity) {
    expect(fn () => new ShoppingNeed(11, $quantity))->toThrow(ContractException::class);
})->with(['0', '-1', '1,5', '1.2345', 'viele']);

it('describes a need', function () {
    expect((new ShoppingNeed(11, '2.5', 'Webshop'))->toArray())->toBe(['article_id' => 11, 'quantity' => '2.5', 'note' => 'Webshop']);
});
