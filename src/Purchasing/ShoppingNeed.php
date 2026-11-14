<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Purchasing;

use MyTechIO\Contracts\ContractException;

/**
 * Something a module needs bought: an article of the core and how many of
 * it (a positive decimal string, at most three decimals). `note` says what
 * for, in a few words.
 */
final readonly class ShoppingNeed
{
    public function __construct(
        public int $articleId,
        public string $quantity,
        public ?string $note = null,
    ) {
        if (preg_match('/^\d{1,9}(\.\d{1,3})?$/', $quantity) !== 1 || (float) $quantity <= 0) {
            throw new ContractException("Ungültige Menge: {$quantity}.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['article_id' => $this->articleId, 'quantity' => $this->quantity, 'note' => $this->note];
    }
}
