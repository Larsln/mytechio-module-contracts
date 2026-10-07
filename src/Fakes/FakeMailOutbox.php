<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Mail\MailOutbox;
use MyTechIO\Contracts\Mail\OutgoingMail;
use MyTechIO\Contracts\Mail\SentMail;

/**
 * Test double for `MailOutbox`: doesn't actually send anything, but
 * remembers every mail. `sent()`/`sentTo()` are deliberately simple methods
 * without a PHPUnit dependency, so they can also be used outside of
 * Pest assertions.
 */
final class FakeMailOutbox implements MailOutbox
{
    /**
     * @var list<OutgoingMail>
     */
    private array $mails = [];

    private int $nextOutboundMailId = 1;

    public function send(OutgoingMail $mail): SentMail
    {
        $this->mails[] = $mail;

        return new SentMail($this->nextOutboundMailId++);
    }

    /**
     * @return list<OutgoingMail>
     */
    public function sent(): array
    {
        return $this->mails;
    }

    /**
     * @return list<OutgoingMail>
     */
    public function sentTo(string $email): array
    {
        return array_values(array_filter(
            $this->mails,
            static function (OutgoingMail $mail) use ($email): bool {
                foreach ($mail->recipients as $recipient) {
                    if ($recipient->email === $email) {
                        return true;
                    }
                }

                return false;
            },
        ));
    }
}
