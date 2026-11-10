<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\ArticleComponentData;

it('converts to an array with snake_case keys', function () {
    expect((new ArticleComponentData(9, 'ART-0009', 'Domain', '2.0000'))->toArray())->toBe([
        'article_id' => 9,
        'article_number' => 'ART-0009',
        'name' => 'Domain',
        'quantity' => '2.0000',
    ]);
});
