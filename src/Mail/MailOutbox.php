<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Sending of emails from modules.
 *
 * Core semantics: the email is rendered with the branding layout and the
 * usual signature, recorded in the outbox (Postausgang), and fired as the
 * core event `MailSent`. Modules never send directly via `Mail::` or
 * similar — every send goes through this contract so that the outbox and
 * audit trail remain complete.
 */
interface MailOutbox
{
    public function send(OutgoingMail $mail): SentMail;
}
