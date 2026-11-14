<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

use MyTechIO\Contracts\ContractException;

/** A shipment could not be created or changed; the message is German and meant for staff. */
final class ShipmentException extends ContractException {}
