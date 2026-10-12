<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\FiscalProfile;
use MyTechIO\Contracts\Fiscal\FiscalProfiles;
use MyTechIO\Contracts\Fiscal\UnknownFiscalProfileException;

/**
 * Test double for `FiscalProfiles`: an in-memory registry. Without arguments it
 * holds one `FakeFiscalProfile` as the current profile; `current()` throws
 * `UnknownFiscalProfileException` when the fixed code is not registered.
 */
final class FakeFiscalProfiles implements FiscalProfiles
{
    /**
     * @var array<string, FiscalProfile>
     */
    private array $profiles = [];

    private string $currentCode;

    /**
     * @param  list<FiscalProfile>  $profiles
     * @param  string|null  $currentCode  the tenant's profile; the first profile when omitted
     */
    public function __construct(array $profiles = [], ?string $currentCode = null)
    {
        foreach ($profiles === [] ? [new FakeFiscalProfile] : $profiles as $profile) {
            $this->profiles[$profile->code()] = $profile;
        }

        $this->currentCode = $currentCode ?? array_key_first($this->profiles);
    }

    public function current(): FiscalProfile
    {
        return $this->profiles[$this->currentCode] ?? throw new UnknownFiscalProfileException($this->currentCode);
    }

    public function find(string $code): ?FiscalProfile
    {
        return $this->profiles[$code] ?? null;
    }

    public function all(): array
    {
        return array_values($this->profiles);
    }

    /**
     * Fixes the tenant's profile to another code.
     */
    public function fix(string $code): self
    {
        $this->currentCode = $code;

        return $this;
    }
}
