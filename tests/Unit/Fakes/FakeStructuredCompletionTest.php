<?php

declare(strict_types=1);

use MyTechIO\Contracts\Ai\StructuredCompletion;
use MyTechIO\Contracts\Ai\StructuredCompletionException;
use MyTechIO\Contracts\Fakes\FakeStructuredCompletion;

it('exposes defaults for key, label and configured state', function () {
    $completion = new FakeStructuredCompletion;

    expect($completion)->toBeInstanceOf(StructuredCompletion::class)
        ->and($completion->completionKey())->toBe('fake-completion')
        ->and($completion->label())->toBe('Fake-KI')
        ->and($completion->configured())->toBeTrue();
});

it('returns the configured answer and records every call', function () {
    $completion = (new FakeStructuredCompletion)->returns(['delimiter' => ';']);
    $schema = ['type' => 'object'];

    $answer = $completion->complete('Erzeuge ein Profil.', '<TEXT len=4>', $schema);

    expect($answer)->toBe(['delimiter' => ';'])
        ->and($completion->calls())->toHaveCount(1)
        ->and($completion->calls()[0]['instruction'])->toBe('Erzeuge ein Profil.')
        ->and($completion->calls()[0]['data'])->toBe('<TEXT len=4>')
        ->and($completion->calls()[0]['schema'])->toBe($schema);
});

it('throws the configured exception', function () {
    $completion = (new FakeStructuredCompletion)->throws(new StructuredCompletionException('kaputt'));

    expect(fn () => $completion->complete('i', 'd', []))->toThrow(StructuredCompletionException::class, 'kaputt');
});

it('allows toggling the configured state', function () {
    expect((new FakeStructuredCompletion)->withConfigured(false)->configured())->toBeFalse();
});
