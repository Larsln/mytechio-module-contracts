<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

enum PublicLinkStatus: string
{
    case Active = 'active';
    /** A single-use link that was followed once. */
    case Used = 'used';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktiv',
            self::Used => 'Verwendet',
            self::Expired => 'Abgelaufen',
            self::Revoked => 'Widerrufen',
        };
    }
}
