<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Ergebnis eines erfolgreichen Versands.
 *
 * `outboundMailId` ist die ID des Postausgang-Eintrags im Kern.
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
