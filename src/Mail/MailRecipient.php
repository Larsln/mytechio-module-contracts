<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Ein Empfänger einer modulinitiierten Mail.
 *
 * `role` unterscheidet To/Cc/Bcc (`'to'|'cc'|'bcc'`, Default `'to'`);
 * `contactId` verknüpft den Empfänger — falls bekannt — mit einem Kontakt
 * des Kerns, rein informativ für den Postausgang.
 */
final readonly class MailRecipient
{
    public function __construct(
        public string $email,
        public string $displayName,
        public ?int $contactId = null,
        public string $role = 'to',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'display_name' => $this->displayName,
            'contact_id' => $this->contactId,
            'role' => $this->role,
        ];
    }
}
