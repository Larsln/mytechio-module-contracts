<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `Stock` method is called with an unknown article ID.
 */
final class StockArticleNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $articleId,
    ) {
        parent::__construct(sprintf('Artikel mit ID %d wurde nicht gefunden.', $articleId));
    }
}
