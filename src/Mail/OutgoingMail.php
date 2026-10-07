<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * An email requested by a module.
 *
 * `ownerType`/`ownerId` reference the business object the email belongs to
 * (for the outbox); `template` is an optional hint to the implementation
 * about which branding layout/template to use (without a template, the
 * core renders a generic layout).
 */
final readonly class OutgoingMail
{
    /**
     * @param  list<MailRecipient>  $recipients
     * @param  list<MailAttachment>  $attachments
     */
    public function __construct(
        public string $subject,
        public string $bodyHtml,
        public array $recipients,
        public array $attachments = [],
        public ?string $ownerType = null,
        public ?int $ownerId = null,
        public ?string $template = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'body_html' => $this->bodyHtml,
            'recipients' => array_map(static fn (MailRecipient $recipient) => $recipient->toArray(), $this->recipients),
            'attachments' => array_map(static fn (MailAttachment $attachment) => $attachment->toArray(), $this->attachments),
            'owner_type' => $this->ownerType,
            'owner_id' => $this->ownerId,
            'template' => $this->template,
        ];
    }
}
