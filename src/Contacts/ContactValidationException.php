<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown by {@see Contacts::create()} and {@see Contacts::update()} when the
 * core refuses the data: `errors` holds the German messages per field (keys
 * as in {@see NewContact::toArray()}), so a module can show them next to its
 * own form fields; `getMessage()` joins them.
 */
final class ContactValidationException extends ContractException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        public readonly array $errors,
    ) {
        parent::__construct(implode(' ', array_merge(...array_values($errors))) ?: 'Die Kontaktdaten sind ungültig.');
    }

    public static function forField(string $field, string $message): self
    {
        return new self([$field => [$message]]);
    }
}
