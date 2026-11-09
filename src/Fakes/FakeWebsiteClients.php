<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Web\WebsiteAuthResult;
use MyTechIO\Contracts\Web\WebsiteClients;

/**
 * In-memory {@see WebsiteClients} for module tests: keys are registered with
 * {@see seed()} (optionally with an allowlist of exact IPs and CIDR ranges,
 * IPv4 and IPv6), revoked with {@see revoke()}, and every check is recorded
 * in {@see calls()} without the secret.
 */
final class FakeWebsiteClients implements WebsiteClients
{
    /** @var array<string, array{client_id: int, client_name: string, ip_allowlist: list<string>, revoked: bool}> */
    private array $keys = [];

    /** @var list<array{client_id: int|null, ip: string|null, status: string}> */
    private array $calls = [];

    public function __construct(
        private bool $available = true,
    ) {}

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * @param  list<string>  $ipAllowlist  exact IPs or CIDR ranges; empty admits every address
     */
    public function seed(string $secret, int $clientId, string $clientName, array $ipAllowlist = []): void
    {
        $this->keys[$secret] = [
            'client_id' => $clientId,
            'client_name' => $clientName,
            'ip_allowlist' => array_values($ipAllowlist),
            'revoked' => false,
        ];
    }

    public function revoke(string $secret): void
    {
        if (isset($this->keys[$secret])) {
            $this->keys[$secret]['revoked'] = true;
        }
    }

    public function available(): bool
    {
        return $this->available;
    }

    public function authenticate(string $secret, ?string $ip): WebsiteAuthResult
    {
        $result = $this->check($secret, $ip);
        $this->calls[] = ['client_id' => $result->clientId, 'ip' => $ip, 'status' => $result->status->value];

        return $result;
    }

    /**
     * @return list<array{client_id: int|null, ip: string|null, status: string}>
     */
    public function calls(): array
    {
        return $this->calls;
    }

    private function check(string $secret, ?string $ip): WebsiteAuthResult
    {
        if (! $this->available) {
            return WebsiteAuthResult::unavailable();
        }

        $key = $this->keys[$secret] ?? null;

        if ($key === null || $key['revoked']) {
            return WebsiteAuthResult::unknownKey();
        }

        if (! self::allows($key['ip_allowlist'], $ip)) {
            return WebsiteAuthResult::ipDenied($key['client_id'], $key['client_name']);
        }

        return WebsiteAuthResult::ok($key['client_id'], $key['client_name']);
    }

    /**
     * @param  list<string>  $entries
     */
    private static function allows(array $entries, ?string $ip): bool
    {
        if ($entries === []) {
            return true;
        }

        if ($ip === null) {
            return false;
        }

        foreach ($entries as $entry) {
            if (self::matches($entry, $ip)) {
                return true;
            }
        }

        return false;
    }

    private static function matches(string $entry, string $ip): bool
    {
        if (! str_contains($entry, '/')) {
            return self::packed($entry) === self::packed($ip);
        }

        [$network, $bits] = explode('/', $entry, 2);
        $networkPacked = self::packed($network);
        $ipPacked = self::packed($ip);

        if ($networkPacked === null || $ipPacked === null || strlen($networkPacked) !== strlen($ipPacked) || ! ctype_digit($bits)) {
            return false;
        }

        $prefix = (int) $bits;
        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        if (substr($networkPacked, 0, $fullBytes) !== substr($ipPacked, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($networkPacked[$fullBytes]) & $mask) === (ord($ipPacked[$fullBytes]) & $mask);
    }

    private static function packed(string $address): ?string
    {
        $packed = @inet_pton($address);

        return $packed === false ? null : $packed;
    }
}
