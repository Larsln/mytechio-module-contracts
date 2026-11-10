<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * A marker a module puts on contacts in the core's contact list, e.g. "has a
 * shop login": the list shows the symbol next to the name (the detail as its
 * tooltip) and offers the marker as a filter.
 *
 * The core collects implementations via the container tag
 * `mytechio.contact_markers`
 * (`$this->app->tag([ShopContactMarker::class], ContactMarker::TAG)`) and
 * skips markers of a module that is disabled or incompatible. Both lookups
 * are reads on the contact list and must not change anything; `contactIds()`
 * may be called with the whole list in view, so keep it one query.
 */
interface ContactMarker
{
    public const string TAG = 'mytechio.contact_markers';

    /** Stable key, used as the filter value, e.g. `shop`. */
    public function key(): string;

    /** German label for the filter and the tooltip, e.g. `Shop-Konto`. */
    public function label(): string;

    /** One or two characters shown next to the name, e.g. `S`. */
    public function symbol(): string;

    /**
     * The marked contacts among the given ones, with an optional German
     * detail per contact (e.g. the account status) for the tooltip.
     *
     * @param  list<int>  $contactIds
     * @return array<int, string|null> contact id => detail
     */
    public function detailsFor(array $contactIds): array;

    /**
     * Every contact carrying the marker, for the list filter.
     *
     * @return list<int>
     */
    public function contactIds(): array;
}
