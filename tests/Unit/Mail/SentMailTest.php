<?php

declare(strict_types=1);

use MyTechIO\Contracts\Mail\SentMail;

it('converts to an array with snake_case keys', function () {
    $sent = new SentMail(outboundMailId: 9);

    expect($sent->toArray())->toBe(['outbound_mail_id' => 9]);
});
