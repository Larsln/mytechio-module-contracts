<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakePublicLinkResolver;
use MyTechIO\Contracts\Fakes\FakePublicLinks;
use MyTechIO\Contracts\Web\LinkTarget;
use MyTechIO\Contracts\Web\NewPublicLink;
use MyTechIO\Contracts\Web\PublicLinks;
use MyTechIO\Contracts\Web\PublicLinkStatus;
use MyTechIO\Contracts\Web\PublicLinksUnavailableException;

function newMandateLink(array $overrides = []): NewPublicLink
{
    return new NewPublicLink(...[
        'kind' => 'stripe.mandate',
        'subjectType' => 'contact',
        'subjectId' => '17',
        'payload' => ['customer' => 'cus_1'],
        ...$overrides,
    ]);
}

it('issues links with predictable tokens and the public url', function () {
    $links = new FakePublicLinks(baseUrl: 'https://mytechio.example/l');

    $link = $links->create(newMandateLink(['expiresAt' => new DateTimeImmutable('2026-12-01')]));

    expect($links)->toBeInstanceOf(PublicLinks::class)
        ->and($link->token)->toBe('link-1')
        ->and($link->url)->toBe('https://mytechio.example/l/link-1')
        ->and($link->status)->toBe(PublicLinkStatus::Active)
        ->and($link->toArray()['subject_id'])->toBe('17')
        ->and($links->find('link-1')?->kind)->toBe('stripe.mandate')
        ->and($links->find('missing'))->toBeNull();
});

it('refuses to issue a link while unavailable', function () {
    $links = new FakePublicLinks(available: false);

    expect($links->available())->toBeFalse();

    $links->create(newMandateLink());
})->throws(PublicLinksUnavailableException::class);

it('lists and revokes the active links of a subject, newest first', function () {
    $links = new FakePublicLinks;
    $links->create(newMandateLink());
    $links->create(newMandateLink());
    $links->create(newMandateLink(['subjectId' => '18']));
    $links->revoke('link-1', 'ersetzt');

    expect(array_map(fn ($link) => $link->token, $links->forSubject('stripe.mandate', 'contact', '17')))->toBe(['link-2'])
        ->and($links->revokeForSubject('stripe.mandate', 'contact', '17'))->toBe(1)
        ->and($links->find('link-2')?->status)->toBe(PublicLinkStatus::Revoked)
        ->and($links->find('link-3')?->status)->toBe(PublicLinkStatus::Active);
});

it('follows a link through the resolver of its kind and counts the click', function () {
    $links = new FakePublicLinks(now: new DateTimeImmutable('2026-11-06T12:00:00+01:00'));
    $resolver = new FakePublicLinkResolver('stripe.mandate');
    $links->resolveWith($resolver);
    $links->create(newMandateLink());

    $target = $links->follow('link-1');

    expect($target->isRedirect())->toBeTrue()
        ->and($resolver->resolved)->toHaveCount(1)
        ->and($links->find('link-1')?->clickCount)->toBe(1)
        ->and($links->find('link-1')?->lastClickedAt?->format(DATE_ATOM))->toBe('2026-11-06T12:00:00+01:00')
        ->and($links->find('link-1')?->status)->toBe(PublicLinkStatus::Active);
});

it('uses up a single-use link on the first redirect', function () {
    $links = new FakePublicLinks;
    $links->resolveWith(new FakePublicLinkResolver('stripe.mandate'));
    $links->create(newMandateLink(['singleUse' => true]));

    expect($links->follow('link-1')->isRedirect())->toBeTrue()
        ->and($links->find('link-1')?->status)->toBe(PublicLinkStatus::Used)
        ->and($links->follow('link-1')->reason)->toBe('used');
});

it('answers invalid for unknown, expired, revoked and unresolvable links', function () {
    $links = new FakePublicLinks(now: new DateTimeImmutable('2026-11-06T12:00:00+01:00'));
    $links->create(newMandateLink(['expiresAt' => new DateTimeImmutable('2026-11-06T11:00:00+01:00')]));
    $links->create(newMandateLink());
    $links->create(newMandateLink(['kind' => 'web.ticket']));
    $links->revoke('link-2');

    expect($links->follow('nope')->reason)->toBe('not_found')
        ->and($links->follow('link-1')->reason)->toBe('expired')
        ->and($links->find('link-1')?->status)->toBe(PublicLinkStatus::Expired)
        ->and($links->follow('link-2')->reason)->toBe('revoked')
        ->and($links->follow('link-3')->reason)->toBe('no_resolver');
});

it('passes an invalid answer of the resolver through without using the link up', function () {
    $links = new FakePublicLinks;
    $resolver = new FakePublicLinkResolver('stripe.mandate');
    $resolver->answerWith(LinkTarget::invalid('already_done', 'Das Mandat wurde bereits erteilt.'));
    $links->resolveWith($resolver);
    $links->create(newMandateLink(['singleUse' => true]));

    expect($links->follow('link-1')->message)->toBe('Das Mandat wurde bereits erteilt.')
        ->and($links->find('link-1')?->status)->toBe(PublicLinkStatus::Active)
        ->and($links->find('link-1')?->clickCount)->toBe(1);
});
