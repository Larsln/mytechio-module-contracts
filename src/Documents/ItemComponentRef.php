<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * A component of a package line on a document.
 *
 * `quantity` is a decimal string and already multiplied by the quantity of the
 * package line. `object` is the object value of the component's subscription
 * (e.g. a domain name); it is `null` on plain document lines, which have no
 * subscriptions yet.
 */
final readonly class ItemComponentRef
{
    public function __construct(
        public int $articleId,
        public string $articleNumber,
        public string $name,
        public string $quantity,
        public ?string $unit = null,
        public ?string $object = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'article_number' => $this->articleNumber,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'object' => $this->object,
        ];
    }
}
