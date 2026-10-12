<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

use MyTechIO\Contracts\ContractException;

/**
 * The profile's account map has no account for a role (and rate/scenario).
 */
final class AccountNotMappedException extends ContractException
{
    public function __construct(
        public readonly AccountRole $role,
        public readonly ?string $rateKey = null,
        public readonly ?string $scenario = null,
    ) {
        parent::__construct(sprintf(
            'Kein Konto gemappt für die Rolle „%s“%s%s.',
            $role->value,
            $scenario !== null ? sprintf(', Szenario „%s“', $scenario) : '',
            $rateKey !== null ? sprintf(', Satz „%s“', $rateKey) : '',
        ));
    }
}
