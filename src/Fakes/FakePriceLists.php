<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Pricing\CustomerPriceData;
use MyTechIO\Contracts\Pricing\CustomerPriceSource;
use MyTechIO\Contracts\Pricing\NewPriceList;
use MyTechIO\Contracts\Pricing\NewPriceListEntry;
use MyTechIO\Contracts\Pricing\PriceListData;
use MyTechIO\Contracts\Pricing\PriceListEntryData;
use MyTechIO\Contracts\Pricing\PriceListException;
use MyTechIO\Contracts\Pricing\PriceListNotFoundException;
use MyTechIO\Contracts\Pricing\PriceLists;

/**
 * Test double for `PriceLists`: in-memory store with ascending ids and the
 * core's rules. An entry names at most one target, carries exactly one of
 * fixed price and discount, a fixed price needs an article or price option,
 * and a target appears once per list. The fake does not know contacts, so
 * `assign()` accepts any contact id.
 *
 * `resolve()` applies the core's precedence: fixed price of the option, fixed
 * price of the article (only for the article's default option), percentage
 * of the option, article, product group and the whole list, the contact's
 * default discount, none. Inactive lists are ignored. Because the fake has
 * no article catalogue, a test declares the options it resolves with
 * `registerOption()` (list price, article, product group, default flag) and
 * optional contact discounts with `setDefaultDiscount()`; an undeclared
 * option raises `PriceListNotFoundException`. Entry targets are not checked
 * against the catalogue. Writes record their actor in `actors()`.
 */
final class FakePriceLists implements PriceLists
{
    /**
     * @var array<int, array{name: string, description: ?string, isActive: bool}>
     */
    private array $lists = [];

    /**
     * @var array<int, PriceListEntryData>
     */
    private array $entries = [];

    /**
     * @var array<int, int> contact id => price list id
     */
    private array $assignments = [];

    /**
     * @var array<int, array{listPriceNet: string, articleId: ?int, productGroupId: ?int, isDefault: bool}>
     */
    private array $options = [];

    /**
     * @var array<int, string>
     */
    private array $defaultDiscounts = [];

    /**
     * @var list<array{action: string, id: int, actor: ActorRef}>
     */
    private array $actors = [];

    private int $nextListId = 1;

    private int $nextEntryId = 1;

    /**
     * Declares a price option for `resolve()`.
     *
     * @param  string  $listPriceNet  decimal string, the price of the option for the contact's price class
     */
    public function registerOption(int $priceOptionId, string $listPriceNet, ?int $articleId = null, ?int $productGroupId = null, bool $isDefault = true): void
    {
        $this->options[$priceOptionId] = [
            'listPriceNet' => $listPriceNet,
            'articleId' => $articleId,
            'productGroupId' => $productGroupId,
            'isDefault' => $isDefault,
        ];
    }

    /**
     * Sets the customer profile discount of a contact (decimal string).
     */
    public function setDefaultDiscount(int $contactId, string $percent): void
    {
        $this->defaultDiscounts[$contactId] = $percent;
    }

    public function find(int $id): ?PriceListData
    {
        return isset($this->lists[$id]) ? $this->data($id) : null;
    }

    public function all(bool $includeInactive = false): array
    {
        $ids = array_keys(array_filter(
            $this->lists,
            static fn (array $list): bool => $includeInactive || $list['isActive'],
        ));
        usort($ids, fn (int $a, int $b): int => strcmp($this->lists[$a]['name'], $this->lists[$b]['name']) ?: $a <=> $b);

        return array_map(fn (int $id): PriceListData => $this->data($id), $ids);
    }

    public function forContact(int $contactId): ?PriceListData
    {
        return isset($this->assignments[$contactId]) ? $this->data($this->assignments[$contactId]) : null;
    }

    public function resolve(int $contactId, int $priceOptionId): CustomerPriceData
    {
        $option = $this->options[$priceOptionId] ?? throw new PriceListNotFoundException('Preisoption', $priceOptionId);
        $listPrice = $this->scaled($option['listPriceNet'], 4);
        $listId = $this->assignments[$contactId] ?? null;
        $entries = $listId !== null && $this->lists[$listId]['isActive']
            ? array_values(array_filter($this->entries, static fn (PriceListEntryData $entry): bool => $entry->priceListId === $listId))
            : [];

        $fixedByOption = $this->firstEntry($entries, static fn (PriceListEntryData $entry): bool => $entry->fixedPriceNet !== null && $entry->priceOptionId === $priceOptionId);
        $fixedByArticle = $option['isDefault'] && $option['articleId'] !== null
            ? $this->firstEntry($entries, static fn (PriceListEntryData $entry): bool => $entry->fixedPriceNet !== null && $entry->articleId === $option['articleId'])
            : null;
        $fixed = $fixedByOption ?? $fixedByArticle;

        if ($fixed !== null) {
            return $this->price($contactId, $priceOptionId, $listPrice, $this->scaled((string) $fixed->fixedPriceNet, 4), '0', $fixedByOption !== null ? CustomerPriceSource::OptionFixed : CustomerPriceSource::ArticleFixed);
        }

        $percentages = [
            [CustomerPriceSource::OptionPercent, static fn (PriceListEntryData $entry): bool => $entry->priceOptionId === $priceOptionId],
            [CustomerPriceSource::ArticlePercent, static fn (PriceListEntryData $entry): bool => $entry->articleId !== null && $entry->articleId === $option['articleId']],
            [CustomerPriceSource::GroupPercent, static fn (PriceListEntryData $entry): bool => $entry->productGroupId !== null && $entry->productGroupId === $option['productGroupId']],
            [CustomerPriceSource::ListPercent, static fn (PriceListEntryData $entry): bool => $entry->productGroupId === null && $entry->articleId === null && $entry->priceOptionId === null],
        ];

        foreach ($percentages as [$source, $matches]) {
            $entry = $this->firstEntry($entries, static fn (PriceListEntryData $candidate): bool => $candidate->discountPercent !== null && $matches($candidate));

            if ($entry !== null) {
                $percent = $this->formatted($this->scaled((string) $entry->discountPercent, 2), 2);

                return $this->price($contactId, $priceOptionId, $listPrice, $this->discounted($listPrice, $percent), $percent, $source);
            }
        }

        $default = $this->defaultDiscounts[$contactId] ?? '0';

        if ($this->scaled($default, 2) > 0) {
            return $this->price($contactId, $priceOptionId, $listPrice, $listPrice, $default, CustomerPriceSource::CustomerDefault);
        }

        return $this->price($contactId, $priceOptionId, $listPrice, $listPrice, '0', CustomerPriceSource::None);
    }

    public function create(NewPriceList $list, ActorRef $actor): PriceListData
    {
        $id = $this->nextListId++;
        $this->lists[$id] = ['name' => $list->name, 'description' => $list->description, 'isActive' => $list->isActive];
        $this->actors[] = ['action' => 'create', 'id' => $id, 'actor' => $actor];

        return $this->data($id);
    }

    public function update(int $id, NewPriceList $list, ActorRef $actor): PriceListData
    {
        $this->assertList($id);
        $this->lists[$id] = ['name' => $list->name, 'description' => $list->description, 'isActive' => $list->isActive];
        $this->actors[] = ['action' => 'update', 'id' => $id, 'actor' => $actor];

        return $this->data($id);
    }

    public function delete(int $id, ActorRef $actor): int
    {
        $this->assertList($id);
        $detached = array_keys(array_filter($this->assignments, static fn (int $listId): bool => $listId === $id));

        foreach ($detached as $contactId) {
            unset($this->assignments[$contactId]);
        }

        $this->entries = array_filter($this->entries, static fn (PriceListEntryData $entry): bool => $entry->priceListId !== $id);
        unset($this->lists[$id]);
        $this->actors[] = ['action' => 'delete', 'id' => $id, 'actor' => $actor];

        return count($detached);
    }

    public function addEntry(int $id, NewPriceListEntry $entry, ActorRef $actor): PriceListEntryData
    {
        $this->assertList($id);
        $this->assertValid($entry);
        $this->assertTargetFree($id, $entry);

        $entryId = $this->nextEntryId++;
        $this->actors[] = ['action' => 'add_entry', 'id' => $entryId, 'actor' => $actor];

        return $this->entries[$entryId] = $this->entryData($entryId, $id, $entry);
    }

    public function updateEntry(int $entryId, NewPriceListEntry $entry, ActorRef $actor): PriceListEntryData
    {
        $existing = $this->entries[$entryId] ?? throw new PriceListNotFoundException('Preislisteneintrag', $entryId);
        $this->assertValid($entry);
        $this->assertTargetFree($existing->priceListId, $entry, $entryId);

        $this->actors[] = ['action' => 'update_entry', 'id' => $entryId, 'actor' => $actor];

        return $this->entries[$entryId] = $this->entryData($entryId, $existing->priceListId, $entry);
    }

    public function removeEntry(int $entryId, ActorRef $actor): void
    {
        if (! isset($this->entries[$entryId])) {
            throw new PriceListNotFoundException('Preislisteneintrag', $entryId);
        }

        unset($this->entries[$entryId]);
        $this->actors[] = ['action' => 'remove_entry', 'id' => $entryId, 'actor' => $actor];
    }

    public function assign(int $contactId, ?int $priceListId, ActorRef $actor): void
    {
        if ($priceListId === null) {
            unset($this->assignments[$contactId]);
        } else {
            $this->assertList($priceListId);
            $this->assignments[$contactId] = $priceListId;
        }

        $this->actors[] = ['action' => 'assign', 'id' => $contactId, 'actor' => $actor];
    }

    /**
     * Recorded actors of all writes in order; `id` is the list id for
     * `create`/`update`/`delete`, the entry id for the entry actions and
     * the contact id for `assign`.
     *
     * @return list<array{action: string, id: int, actor: ActorRef}>
     */
    public function actors(): array
    {
        return $this->actors;
    }

    private function assertList(int $id): void
    {
        if (! isset($this->lists[$id])) {
            throw new PriceListNotFoundException('Preisliste', $id);
        }
    }

    private function assertValid(NewPriceListEntry $entry): void
    {
        $targets = count(array_filter([$entry->productGroupId, $entry->articleId, $entry->priceOptionId], static fn (?int $id): bool => $id !== null));

        if ($targets > 1) {
            throw new PriceListException('Ein Eintrag gilt für genau ein Ziel: Produktgruppe, Artikel oder Preisoption.');
        }

        if (($entry->fixedPriceNet === null) === ($entry->discountPercent === null)) {
            throw new PriceListException('Ein Eintrag hat entweder einen Festpreis oder einen Rabatt.');
        }

        if ($entry->fixedPriceNet !== null && $entry->articleId === null && $entry->priceOptionId === null) {
            throw new PriceListException('Ein Festpreis gilt für einen Artikel oder eine Preisoption.');
        }
    }

    private function assertTargetFree(int $listId, NewPriceListEntry $entry, ?int $exceptEntryId = null): void
    {
        foreach ($this->entries as $existing) {
            if ($existing->priceListId === $listId && $existing->id !== $exceptEntryId
                && $existing->productGroupId === $entry->productGroupId
                && $existing->articleId === $entry->articleId
                && $existing->priceOptionId === $entry->priceOptionId) {
                throw new PriceListException('Für dieses Ziel gibt es in der Preisliste bereits einen Eintrag.');
            }
        }
    }

    private function entryData(int $entryId, int $listId, NewPriceListEntry $entry): PriceListEntryData
    {
        return new PriceListEntryData(
            id: $entryId,
            priceListId: $listId,
            productGroupId: $entry->productGroupId,
            articleId: $entry->articleId,
            priceOptionId: $entry->priceOptionId,
            fixedPriceNet: $entry->fixedPriceNet,
            discountPercent: $entry->discountPercent,
        );
    }

    private function data(int $id): PriceListData
    {
        $list = $this->lists[$id];
        $contactIds = array_keys(array_filter($this->assignments, static fn (int $listId): bool => $listId === $id));
        sort($contactIds);

        return new PriceListData(
            id: $id,
            name: $list['name'],
            description: $list['description'],
            isActive: $list['isActive'],
            entries: array_values(array_filter($this->entries, static fn (PriceListEntryData $entry): bool => $entry->priceListId === $id)),
            contactIds: $contactIds,
        );
    }

    /**
     * @param  list<PriceListEntryData>  $entries
     * @param  callable(PriceListEntryData): bool  $matches
     */
    private function firstEntry(array $entries, callable $matches): ?PriceListEntryData
    {
        foreach ($entries as $entry) {
            if ($matches($entry)) {
                return $entry;
            }
        }

        return null;
    }

    private function price(int $contactId, int $priceOptionId, int $listPrice, int $unitPrice, string $percent, CustomerPriceSource $source): CustomerPriceData
    {
        return new CustomerPriceData($contactId, $priceOptionId, $this->formatted($listPrice, 4), $this->formatted($unitPrice, 4), $percent, $source);
    }

    /**
     * List price reduced by the percentage, in ten-thousandths, rounded half to even.
     *
     * @param  string  $percent  decimal string with two places
     */
    private function discounted(int $listPrice, string $percent): int
    {
        $numerator = $listPrice * (10000 - $this->scaled($percent, 2));
        $quotient = intdiv($numerator, 10000);
        $remainder = $numerator % 10000;

        if ($remainder * 2 > 10000 || ($remainder * 2 === 10000 && $quotient % 2 === 1)) {
            $quotient++;
        }

        return $quotient;
    }

    /**
     * Parses a non-negative decimal string into an integer of 10^-$places units (excess digits truncated).
     */
    private function scaled(string $decimal, int $places): int
    {
        [$whole, $fraction] = array_pad(explode('.', trim($decimal), 2), 2, '');

        return (int) $whole * 10 ** $places + (int) str_pad(substr($fraction, 0, $places), $places, '0');
    }

    private function formatted(int $scaled, int $places): string
    {
        $divisor = 10 ** $places;

        return intdiv($scaled, $divisor).'.'.str_pad((string) ($scaled % $divisor), $places, '0', STR_PAD_LEFT);
    }
}
