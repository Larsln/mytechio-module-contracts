<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * A public link as the holder of `public_links` stores it. `token` is the
 * opaque, unguessable part of the public URL; `url` is the complete public
 * address the website answers (the holder knows the website's base URL).
 */
final readonly class PublicLinkData
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $token,
        public string $url,
        public string $kind,
        public string $subjectType,
        public string $subjectId,
        public array $payload,
        public PublicLinkStatus $status,
        public bool $singleUse,
        public ?\DateTimeImmutable $expiresAt,
        public int $clickCount,
        public ?\DateTimeImmutable $lastClickedAt,
        public \DateTimeImmutable $createdAt,
    ) {}

    public function isActive(): bool
    {
        return $this->status === PublicLinkStatus::Active;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'url' => $this->url,
            'kind' => $this->kind,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'payload' => $this->payload,
            'status' => $this->status->value,
            'single_use' => $this->singleUse,
            'expires_at' => $this->expiresAt?->format(DATE_ATOM),
            'click_count' => $this->clickCount,
            'last_clicked_at' => $this->lastClickedAt?->format(DATE_ATOM),
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}
