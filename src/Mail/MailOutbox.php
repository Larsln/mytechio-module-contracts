<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Mail;

/**
 * Versand von Mails aus Modulen heraus.
 *
 * Kern-Semantik: Die Mail wird mit dem Branding-Layout und der üblichen
 * Signatur gerendert, im Postausgang verzeichnet und als Kern-Ereignis
 * `MailSent` gefeuert. Module versenden niemals direkt über `Mail::` o. Ä.
 * — jeder Versand läuft über diesen Vertrag, damit Postausgang und Audit
 * vollständig bleiben.
 */
interface MailOutbox
{
    public function send(OutgoingMail $mail): SentMail;
}
