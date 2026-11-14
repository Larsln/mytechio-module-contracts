<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

/**
 * Outcome of a website key check by {@see WebsiteClients::authenticate()}.
 */
enum WebsiteAuthStatus: string
{
    case Ok = 'ok';
    /** The key is missing, unknown or revoked. */
    case UnknownKey = 'unknown_key';
    /** The key exists, but the request IP is outside its allowlist. */
    case IpDenied = 'ip_denied';
    /** No module holds `public_links`, so no key can be checked at all. */
    case Unavailable = 'unavailable';
}
