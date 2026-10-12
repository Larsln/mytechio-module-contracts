<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Short reference to an article, handed to an `ArticleGuard`.
 */
final readonly class ArticleRef
{
    public function __construct(
        public int $id,
        public string $articleNumber,
        public string $name,
        public bool $isActive,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'article_number' => $this->articleNumber,
            'name' => $this->name,
            'is_active' => $this->isActive,
        ];
    }
}
