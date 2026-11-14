<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

use MyTechIO\Contracts\ContractException;

/** A card payment could not be started, found or changed (German message for staff). */
final class CardPaymentException extends ContractException {}
