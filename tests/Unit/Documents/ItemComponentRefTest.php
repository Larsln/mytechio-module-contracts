<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\ItemComponentRef;

it('defaults unit and object to null', function () {
    $component = new ItemComponentRef(9, 'ART-0009', 'Domain', '2.0000');

    expect($component->unit)->toBeNull()
        ->and($component->object)->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    expect((new ItemComponentRef(9, 'ART-0009', 'Domain', '2.0000', 'Stk', 'example.de'))->toArray())->toBe([
        'article_id' => 9,
        'article_number' => 'ART-0009',
        'name' => 'Domain',
        'quantity' => '2.0000',
        'unit' => 'Stk',
        'object' => 'example.de',
    ]);
});
