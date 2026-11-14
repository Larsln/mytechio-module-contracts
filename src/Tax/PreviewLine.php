<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * One line of a document that does not exist yet, for {@see DocumentPreview}.
 * The same fields decide the tax of an order line (`Orders\NewOrderItem`):
 * quantity and net unit price as decimal strings (up to 4 decimals), the tax
 * category, and for an article line the article, whose tax classification
 * the core then uses.
 */
final readonly class PreviewLine
{
    public function __construct(
        public string $quantity,
        public string $unitPriceNet,
        public int $taxCategoryId,
        public ?int $articleId = null,
        public string $discountPercent = '0',
        public ?string $taxClassification = null,
        public ?string $serviceType = null,
    ) {
        Validate::decimal($quantity, 'quantity');
        Validate::decimal($unitPriceNet, 'unitPriceNet');
        Validate::decimal($discountPercent, 'discountPercent');
    }
}
