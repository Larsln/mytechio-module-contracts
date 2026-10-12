<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Articles\ArticleGuard;
use MyTechIO\Contracts\Articles\ArticleRef;
use MyTechIO\Contracts\ContractException;

/**
 * Test double for `ArticleGuard`: records every `beforeDelete()` call
 * (`deleteCalls`, with the `forced` flag); `vetoDeletion()` makes it throw a
 * `ContractException`, `vetoDeletion(overridable: true)` lets a forced delete
 * pass.
 */
final class FakeArticleGuard implements ArticleGuard
{
    /**
     * @var list<array{article: ArticleRef, forced: bool}>
     */
    public array $deleteCalls = [];

    private ?string $veto = null;

    private bool $overridable = false;

    public function beforeDelete(ArticleRef $article, bool $forced): void
    {
        $this->deleteCalls[] = ['article' => $article, 'forced' => $forced];

        if ($this->veto !== null && ! ($forced && $this->overridable)) {
            throw new ContractException($this->veto);
        }
    }

    public function vetoDeletion(string $message, bool $overridable = false): void
    {
        $this->veto = $message;
        $this->overridable = $overridable;
    }
}
