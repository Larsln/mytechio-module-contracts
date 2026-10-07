<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Result of a successful send.
 *
 * `outboundMailId` is the ID of the outbox entry in the core.
 */
final readonly class SentMail
{
    public function __construct(
        public int $outboundMailId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'outbound_mail_id' => $this->outboundMailId,
        ];
    }
}
