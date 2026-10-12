<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

use MyTechIO\Contracts\ContractException;

/**
 * A tax scenario code the profile does not define.
 */
final class UnknownScenarioException extends ContractException
{
    public function __construct(
        public readonly string $scenarioCode,
        ?string $message = null,
    ) {
        parent::__construct($message ?? sprintf('Das Steuerszenario „%s“ ist im Steuerprofil nicht definiert.', $scenarioCode));
    }
}
