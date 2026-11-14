<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Purchasing;

/**
 * What modules need bought, kept on the shopping list (since 2.19), an
 * exclusive capability (`shopping_list`) a shopping list module holds.
 *
 * A module does not add single items; it states everything it needs right
 * now, under its own `source` key, and the holder makes the list match:
 * new needs appear, quantities follow, and what is no longer needed leaves
 * the list. The holder marks these items with `sourceLabel` so people see
 * where they come from. Items people wrote on the list themselves are never
 * touched. An item of the source that someone ticked off (bought, not in
 * stock yet) covers its quantity until the need is gone.
 *
 * Without a holder the contract is not bound: resolve it optionally.
 */
interface ShoppingNeeds
{
    /**
     * Makes the items of `source` on the shopping list equal to `needs` (one entry per article; several for the
     * same article are summed). An empty list removes everything of the source.
     *
     * @param  string  $source  short key of the module, e.g. `shop`
     * @param  string  $sourceLabel  what people read on the item, e.g. `Webshop`
     * @param  list<ShoppingNeed>  $needs
     */
    public function replace(string $source, string $sourceLabel, array $needs): void;

    /**
     * What is on the list for a source right now: the open quantity per article id.
     *
     * @return array<int, string>
     */
    public function open(string $source): array;
}
