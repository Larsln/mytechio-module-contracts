<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * A component of a package article: the component article and how many of it
 * one package contains (`quantity`, decimal string).
 */
final readonly class ArticleComponentData
{
    public function __construct(
        public int $articleId,
        public string $articleNumber,
        public string $name,
        public string $quantity,
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
        ];
    }
}
