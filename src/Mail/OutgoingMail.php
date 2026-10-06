<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Eine von einem Modul angeforderte Mail.
 *
 * `ownerType`/`ownerId` referenzieren das fachliche Objekt, zu dem die Mail
 * gehört (für den Postausgang); `template` ist ein optionaler Hinweis an
 * die Implementierung, welches Branding-Layout/Template verwendet werden
 * soll (ohne Template rendert der Kern ein generisches Layout).
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
