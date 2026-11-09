<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * The website's API keys, checked on behalf of other modules: a module that
 * exposes its own `/api/web/*` endpoints to the website (e.g. a shop) asks
 * this contract whether the `Authorization: Bearer <key>` of a request is a
 * known, unrevoked key whose IP allowlist admits the request. The keys are
 * issued and revoked by the holder of the exclusive capability `public_links`
 * (the Webpräsenz module); this contract only reads them.
 *
 * The core binds a null implementation that reports `available() === false`
 * and answers every check with `unavailable` while no module holds the
 * capability, so callers can refuse with "temporarily unavailable" rather
 * than "unknown key".
 */
interface WebsiteClients
{
    public function available(): bool;

    /**
     * Checks a raw website key. `$ip` is the address the request reached the
     * admin from (the website's outbound address, not the visitor's); `null`
     * admits the key only when it has no allowlist. Implementations must
     * never log the secret.
     */
    public function authenticate(string $secret, ?string $ip): WebsiteAuthResult;
}
