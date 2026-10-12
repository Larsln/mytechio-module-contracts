<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\ContractException;

/**
 * The document a stock movement or reservation belongs to. `type` is one of
 * `order`, `invoice_item` or `incoming_invoice_item`; other values are stored
 * and returned unchanged. `id` is the document's (or line's) ID in the core.
 */
final readonly class StockReference
{
    public const ORDER = 'order';

    public const INVOICE_ITEM = 'invoice_item';

    public const INCOMING_INVOICE_ITEM = 'incoming_invoice_item';

    public function __construct(
        public string $type,
        public int $id,
    ) {
        if ($type === '') {
            throw new ContractException('type must not be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['type' => $this->type, 'id' => $this->id];
    }
}
