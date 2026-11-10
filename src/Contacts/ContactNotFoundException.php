<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

use MyTechIO\Contracts\ContractException;

/** Thrown by {@see Contacts::update()} for an unknown or deleted contact id. */
final class ContactNotFoundException extends ContractException
{
    public static function forId(int $id): self
    {
        return new self(sprintf('Kontakt mit ID %d wurde nicht gefunden.', $id));
    }
}
