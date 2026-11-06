<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Web;

use MyTechIO\Contracts\ContractException;

/** Thrown when a link is requested while no module holds `public_links`. */
final class PublicLinksUnavailableException extends ContractException {}
