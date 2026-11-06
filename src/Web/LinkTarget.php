<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * What following a public link leads to: a redirect to `url`, or nothing
 * with a machine `reason` (e.g. `already_done`, `expired`, `not_found`) and
 * a German `message` the website may show to the visitor.
 */
final readonly class LinkTarget
{
    private function __construct(
        public ?string $url,
        public ?string $reason,
        public string $message,
        public ?\DateTimeImmutable $validUntil,
    ) {}

    public static function redirect(string $url, ?\DateTimeImmutable $validUntil = null): self
    {
        return new self($url, null, '', $validUntil);
    }

    public static function invalid(string $reason, string $message): self
    {
        return new self(null, $reason, $message, null);
    }

    public function isRedirect(): bool
    {
        return $this->url !== null;
    }

    /**
     * @return array{status: 'redirect'|'invalid', url: string|null, reason: string|null, message: string, valid_until: string|null}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->isRedirect() ? 'redirect' : 'invalid',
            'url' => $this->url,
            'reason' => $this->reason,
            'message' => $this->message,
            'valid_until' => $this->validUntil?->format(DATE_ATOM),
        ];
    }
}
