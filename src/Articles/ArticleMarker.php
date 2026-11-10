<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * A marker a module puts on articles in the core's article list, e.g.
 * "released in the shop": the list shows the symbol next to the name (the
 * detail as its tooltip) and offers the marker as a filter.
 *
 * The core collects implementations via the container tag
 * `mytechio.article_markers`
 * (`$this->app->tag([ShopArticleMarker::class], ArticleMarker::TAG)`) and
 * skips markers of a module that is disabled or incompatible. Both lookups
 * are reads on the article list and must not change anything; `articleIds()`
 * may be called with the whole list in view, so keep it one query.
 */
interface ArticleMarker
{
    public const string TAG = 'mytechio.article_markers';

    /** Stable key, used as the filter value, e.g. `shop`. */
    public function key(): string;

    /** German label for the filter and the tooltip, e.g. `Im Shop`. */
    public function label(): string;

    /** One or two characters shown next to the name, e.g. `S`; the fallback when the icon is unknown. */
    public function symbol(): string;

    /**
     * Name of an icon from the core's module icon map (the names allowed for
     * `navigation[].icon` in the manifest, e.g. `ShoppingCart`), shown instead
     * of the symbol; null shows the symbol.
     */
    public function icon(): ?string;

    /**
     * The marked articles among the given ones, with an optional German
     * detail per article (e.g. the shop address) for the tooltip.
     *
     * @param  list<int>  $articleIds
     * @return array<int, string|null> article id => detail
     */
    public function detailsFor(array $articleIds): array;

    /**
     * Every article carrying the marker, for the list filter.
     *
     * @return list<int>
     */
    public function articleIds(): array;
}
