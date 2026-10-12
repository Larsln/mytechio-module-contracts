<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

use MyTechIO\Contracts\ContractException;

/**
 * The fiscal profile fixed for the tenant is not available (module missing or
 * disabled). The core treats this as a hard error: bookkeeping cannot run
 * without its profile.
 */
final class UnknownFiscalProfileException extends ContractException
{
    public function __construct(
        public readonly string $profileCode,
        ?string $message = null,
    ) {
        parent::__construct($message ?? sprintf('Das Steuerprofil „%s“ ist nicht verfügbar – das zugehörige Modul fehlt oder ist deaktiviert.', $profileCode));
    }
}
