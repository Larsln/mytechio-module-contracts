<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

use MyTechIO\Contracts\ContractException;

/**
 * A module's veto over article changes that its own records depend on (for
 * example the stock module refuses to delete an article that still has stock).
 *
 * The core collects implementations via the container tag
 * `mytechio.article_guards` and calls every guard of an enabled, compatible
 * module before the change; guards of a module that is disabled or incompatible
 * are skipped.
 */
interface ArticleGuard
{
    /**
     * Called before an article is (soft) deleted, nothing has been changed yet.
     * `$forced` is true when the acting user holds the permission to override
     * protections (`articles.force_delete`); the guard decides whether that
     * overrides its veto.
     *
     * @throws ContractException to refuse the deletion (German message, shown to the user).
     */
    public function beforeDelete(ArticleRef $article, bool $forced): void;
}
