<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `PriceLists` write is refused. The message is the core's
 * German validation message and can be shown to the user as it is.
 */
final class PriceListException extends ContractException {}
