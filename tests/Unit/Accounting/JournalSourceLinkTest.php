<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\JournalSourceLink;
use MyTechIO\Contracts\ContractException;

it('converts to an array', function () {
    expect((new JournalSourceLink('LB-2026-00001', '/stock-vouchers/LB-2026-00001'))->toArray())->toBe([
        'label' => 'LB-2026-00001',
        'url' => '/stock-vouchers/LB-2026-00001',
    ]);
});

it('refuses an empty label', function () {
    new JournalSourceLink('  ', '/stock-vouchers/1');
})->throws(ContractException::class);

it('refuses a url that is not relative to the core', function () {
    new JournalSourceLink('Beleg', 'https://example.test/x');
})->throws(ContractException::class);
