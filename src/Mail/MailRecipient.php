<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * A recipient of a module-initiated email.
 *
 * `role` distinguishes To/Cc/Bcc (`'to'|'cc'|'bcc'`, default `'to'`);
 * `contactId` links the recipient — if known — to a contact in the core,
 * purely informational for the outbox.
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
