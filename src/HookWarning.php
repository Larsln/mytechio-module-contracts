<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * A non-blocking remark a document hook hands back to the core, which shows it
 * to the user next to the result of the action (for example "article X: needed
 * 5, available 2 — the stock is now negative"). `code` is a stable
 * machine-readable key (`stock.negative`), `message` the German text for the
 * user, `articleId` the article the remark is about, if any.
 */
final readonly class HookWarning
{
    public function __construct(
        public string $code,
        public string $message,
        public ?int $articleId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'article_id' => $this->articleId,
        ];
    }
}
