<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * Answer of {@see WebsiteClients::authenticate()}: the status and, when the
 * key was found, the website client it belongs to (`clientId` is the key's
 * ID at the holder, `clientName` the name an admin gave the key). The secret
 * itself is never part of the answer.
 */
final readonly class WebsiteAuthResult
{
    private function __construct(
        public WebsiteAuthStatus $status,
        public ?int $clientId,
        public ?string $clientName,
    ) {}

    public static function ok(int $clientId, string $clientName): self
    {
        return new self(WebsiteAuthStatus::Ok, $clientId, $clientName);
    }

    public static function unknownKey(): self
    {
        return new self(WebsiteAuthStatus::UnknownKey, null, null);
    }

    public static function ipDenied(int $clientId, string $clientName): self
    {
        return new self(WebsiteAuthStatus::IpDenied, $clientId, $clientName);
    }

    public static function unavailable(): self
    {
        return new self(WebsiteAuthStatus::Unavailable, null, null);
    }

    public function isOk(): bool
    {
        return $this->status === WebsiteAuthStatus::Ok;
    }

    /**
     * @return array{status: string, client_id: int|null, client_name: string|null}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
        ];
    }
}
