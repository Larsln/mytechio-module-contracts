<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Fakes\FakePriceLists;
use MyTechIO\Contracts\Pricing\CustomerPriceSource;
use MyTechIO\Contracts\Pricing\NewPriceList;
use MyTechIO\Contracts\Pricing\NewPriceListEntry;
use MyTechIO\Contracts\Pricing\PriceListException;
use MyTechIO\Contracts\Pricing\PriceListNotFoundException;

function priceActor(): ActorRef
{
    return new ActorRef(1, 'Lars');
}

it('creates, updates, finds and lists price lists', function () {
    $lists = new FakePriceLists;
    $partner = $lists->create(new NewPriceList('Partner', 'Resellers'), priceActor());
    $lists->create(new NewPriceList('Archiv', isActive: false), priceActor());
    $lists->create(new NewPriceList('Alpha'), priceActor());

    expect($partner->id)->toBe(1)
        ->and($lists->find(1)?->name)->toBe('Partner')
        ->and($lists->find(99))->toBeNull()
        ->and(array_map(fn ($list) => $list->name, $lists->all()))->toBe(['Alpha', 'Partner'])
        ->and(array_map(fn ($list) => $list->name, $lists->all(includeInactive: true)))->toBe(['Alpha', 'Archiv', 'Partner']);

    $updated = $lists->update(1, new NewPriceList('Partner 2', null, false), priceActor());

    expect($updated->name)->toBe('Partner 2')
        ->and($updated->isActive)->toBeFalse()
        ->and(fn () => $lists->update(99, new NewPriceList('x'), priceActor()))->toThrow(PriceListNotFoundException::class);
});

it('adds, updates and removes entries', function () {
    $lists = new FakePriceLists;
    $list = $lists->create(new NewPriceList('Partner'), priceActor());

    $entry = $lists->addEntry($list->id, new NewPriceListEntry(articleId: 7, fixedPriceNet: '9.50'), priceActor());
    $changed = $lists->updateEntry($entry->id, new NewPriceListEntry(articleId: 7, discountPercent: '5'), priceActor());

    expect($changed->discountPercent)->toBe('5')
        ->and($changed->fixedPriceNet)->toBeNull()
        ->and($lists->find($list->id)?->entries)->toHaveCount(1);

    $lists->removeEntry($entry->id, priceActor());

    expect($lists->find($list->id)?->entries)->toBe([])
        ->and(fn () => $lists->removeEntry($entry->id, priceActor()))->toThrow(PriceListNotFoundException::class)
        ->and(fn () => $lists->updateEntry(99, new NewPriceListEntry(discountPercent: '5'), priceActor()))->toThrow(PriceListNotFoundException::class)
        ->and(fn () => $lists->addEntry(99, new NewPriceListEntry(discountPercent: '5'), priceActor()))->toThrow(PriceListNotFoundException::class);
});

it('refuses invalid entries with the core messages', function (NewPriceListEntry $entry, string $message) {
    $lists = new FakePriceLists;
    $list = $lists->create(new NewPriceList('Partner'), priceActor());

    expect(fn () => $lists->addEntry($list->id, $entry, priceActor()))->toThrow(PriceListException::class, $message);
})->with([
    'two targets' => [new NewPriceListEntry(articleId: 1, priceOptionId: 2, discountPercent: '5'), 'genau ein Ziel'],
    'neither price nor discount' => [new NewPriceListEntry(articleId: 1), 'entweder einen Festpreis oder einen Rabatt'],
    'both price and discount' => [new NewPriceListEntry(articleId: 1, fixedPriceNet: '1', discountPercent: '5'), 'entweder einen Festpreis oder einen Rabatt'],
    'fixed price on a group' => [new NewPriceListEntry(productGroupId: 1, fixedPriceNet: '1'), 'Festpreis gilt für einen Artikel'],
    'fixed price on the whole list' => [new NewPriceListEntry(fixedPriceNet: '1'), 'Festpreis gilt für einen Artikel'],
]);

it('keeps a target unique per list but allows it on another list and on its own entry', function () {
    $lists = new FakePriceLists;
    $first = $lists->create(new NewPriceList('A'), priceActor());
    $second = $lists->create(new NewPriceList('B'), priceActor());
    $entry = $lists->addEntry($first->id, new NewPriceListEntry(articleId: 7, discountPercent: '5'), priceActor());

    $lists->addEntry($second->id, new NewPriceListEntry(articleId: 7, discountPercent: '5'), priceActor());
    $lists->updateEntry($entry->id, new NewPriceListEntry(articleId: 7, discountPercent: '8'), priceActor());

    expect(fn () => $lists->addEntry($first->id, new NewPriceListEntry(articleId: 7, discountPercent: '9'), priceActor()))
        ->toThrow(PriceListException::class, 'bereits einen Eintrag');
});

it('assigns contacts, reports them on the list and detaches them on delete', function () {
    $lists = new FakePriceLists;
    $list = $lists->create(new NewPriceList('Partner'), priceActor());
    $lists->assign(9, $list->id, priceActor());
    $lists->assign(5, $list->id, priceActor());

    expect($lists->forContact(5)?->id)->toBe($list->id)
        ->and($lists->find($list->id)?->contactIds)->toBe([5, 9])
        ->and(fn () => $lists->assign(5, 99, priceActor()))->toThrow(PriceListNotFoundException::class);

    $lists->assign(5, null, priceActor());

    expect($lists->forContact(5))->toBeNull()
        ->and($lists->delete($list->id, priceActor()))->toBe(1)
        ->and($lists->forContact(9))->toBeNull()
        ->and($lists->find($list->id))->toBeNull()
        ->and(fn () => $lists->delete($list->id, priceActor()))->toThrow(PriceListNotFoundException::class);
});

it('records the actor of every write', function () {
    $lists = new FakePriceLists;
    $list = $lists->create(new NewPriceList('Partner'), priceActor());
    $lists->assign(5, $list->id, priceActor());

    expect(array_map(fn ($call) => [$call['action'], $call['id']], $lists->actors()))->toBe([['create', 1], ['assign', 5]])
        ->and($lists->actors()[0]['actor']->label)->toBe('Lars');
});

it('resolves the price class price without a list or with an inactive one', function () {
    $lists = new FakePriceLists;
    $lists->registerOption(11, '20', articleId: 7);

    $plain = $lists->resolve(5, 11);

    expect($plain->listPriceNet)->toBe('20.0000')
        ->and($plain->unitPriceNet)->toBe('20.0000')
        ->and($plain->discountPercent)->toBe('0')
        ->and($plain->source)->toBe(CustomerPriceSource::None);

    $list = $lists->create(new NewPriceList('Partner', isActive: false), priceActor());
    $lists->addEntry($list->id, new NewPriceListEntry(discountPercent: '50'), priceActor());
    $lists->assign(5, $list->id, priceActor());
    $lists->setDefaultDiscount(5, '3.00');

    $inactive = $lists->resolve(5, 11);

    expect($inactive->source)->toBe(CustomerPriceSource::CustomerDefault)
        ->and($inactive->unitPriceNet)->toBe('20.0000')
        ->and($inactive->discountPercent)->toBe('3.00')
        ->and(fn () => $lists->resolve(5, 99))->toThrow(PriceListNotFoundException::class);
});

it('applies the core precedence when resolving', function () {
    $lists = new FakePriceLists;
    $lists->registerOption(11, '100', articleId: 7, productGroupId: 4);
    $lists->registerOption(12, '100', articleId: 7, productGroupId: 4, isDefault: false);
    $list = $lists->create(new NewPriceList('Partner'), priceActor());
    $lists->assign(5, $list->id, priceActor());
    $lists->setDefaultDiscount(5, '1');
    $entry = fn (NewPriceListEntry $new) => $lists->addEntry($list->id, $new, priceActor());

    $listWide = $entry(new NewPriceListEntry(discountPercent: '5'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '95.0000', 'discountPercent' => '5.00', 'source' => CustomerPriceSource::ListPercent]);

    $group = $entry(new NewPriceListEntry(productGroupId: 4, discountPercent: '10'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '90.0000', 'source' => CustomerPriceSource::GroupPercent]);

    $article = $entry(new NewPriceListEntry(articleId: 7, discountPercent: '15'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '85.0000', 'source' => CustomerPriceSource::ArticlePercent]);

    $option = $entry(new NewPriceListEntry(priceOptionId: 11, discountPercent: '20'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '80.0000', 'source' => CustomerPriceSource::OptionPercent])
        ->and($lists->resolve(5, 12))->toMatchObject(['unitPriceNet' => '85.0000', 'source' => CustomerPriceSource::ArticlePercent]);

    $lists->removeEntry($article->id, priceActor());
    $entry(new NewPriceListEntry(articleId: 7, fixedPriceNet: '70'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '70.0000', 'discountPercent' => '0', 'source' => CustomerPriceSource::ArticleFixed])
        ->and($lists->resolve(5, 12))->toMatchObject(['unitPriceNet' => '90.0000', 'source' => CustomerPriceSource::GroupPercent]);

    $lists->removeEntry($option->id, priceActor());
    $entry(new NewPriceListEntry(priceOptionId: 11, fixedPriceNet: '60'));
    expect($lists->resolve(5, 11))->toMatchObject(['unitPriceNet' => '60.0000', 'source' => CustomerPriceSource::OptionFixed]);

    $lists->removeEntry($group->id, priceActor());
    $lists->removeEntry($listWide->id, priceActor());
    expect($lists->resolve(5, 12))->toMatchObject(['unitPriceNet' => '100.0000', 'discountPercent' => '1', 'source' => CustomerPriceSource::CustomerDefault]);
});

it('rounds a discounted price half to even at four places', function () {
    $lists = new FakePriceLists;
    $lists->registerOption(11, '0.0005');
    $lists->registerOption(12, '0.0015');
    $list = $lists->create(new NewPriceList('Partner'), priceActor());
    $lists->assign(5, $list->id, priceActor());
    $lists->addEntry($list->id, new NewPriceListEntry(discountPercent: '50'), priceActor());

    expect($lists->resolve(5, 11)->unitPriceNet)->toBe('0.0002')
        ->and($lists->resolve(5, 12)->unitPriceNet)->toBe('0.0008');
});
