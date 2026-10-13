<?php

declare(strict_types=1);

use MyTechIO\Contracts\Ai\StructuredCompletions;
use MyTechIO\Contracts\Fakes\FakeStructuredCompletion;
use MyTechIO\Contracts\Fakes\FakeStructuredCompletions;

it('serves no provider by default', function () {
    $resolver = new FakeStructuredCompletions;

    expect($resolver)->toBeInstanceOf(StructuredCompletions::class)
        ->and($resolver->current())->toBeNull()
        ->and($resolver->available())->toBeFalse();
});

it('serves the given provider', function () {
    $provider = new FakeStructuredCompletion;
    $resolver = new FakeStructuredCompletions($provider);

    expect($resolver->current())->toBe($provider)
        ->and($resolver->available())->toBeTrue();
});

it('swaps the provider', function () {
    $resolver = (new FakeStructuredCompletions(new FakeStructuredCompletion))->with(null);

    expect($resolver->available())->toBeFalse();
});
