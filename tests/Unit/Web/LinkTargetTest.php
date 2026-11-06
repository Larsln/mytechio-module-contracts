<?php

declare(strict_types=1);

use MyTechIO\Contracts\Web\LinkTarget;
use MyTechIO\Contracts\Web\PublicLinkStatus;

it('describes a redirect', function () {
    $until = new DateTimeImmutable('2026-11-07T10:00:00+01:00');
    $target = LinkTarget::redirect('https://checkout.stripe.com/c/pay/cs_123', $until);

    expect($target->isRedirect())->toBeTrue()
        ->and($target->toArray())->toBe([
            'status' => 'redirect',
            'url' => 'https://checkout.stripe.com/c/pay/cs_123',
            'reason' => null,
            'message' => '',
            'valid_until' => '2026-11-07T10:00:00+01:00',
        ]);
});

it('describes an invalid link with its reason and message', function () {
    $target = LinkTarget::invalid('already_done', 'Das Mandat wurde bereits erteilt.');

    expect($target->isRedirect())->toBeFalse()
        ->and($target->toArray())->toBe([
            'status' => 'invalid',
            'url' => null,
            'reason' => 'already_done',
            'message' => 'Das Mandat wurde bereits erteilt.',
            'valid_until' => null,
        ]);
});

it('labels every link status in German', function () {
    expect(array_map(fn (PublicLinkStatus $status) => $status->label(), PublicLinkStatus::cases()))
        ->toBe(['Aktiv', 'Verwendet', 'Abgelaufen', 'Widerrufen']);
});
