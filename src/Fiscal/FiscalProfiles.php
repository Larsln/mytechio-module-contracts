<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Registry of the fiscal profiles the installation offers (the in-core one and
 * those of enabled modules). Bound in the container; modules and the core
 * resolve the tenant's profile through `current()`.
 */
interface FiscalProfiles
{
    /**
     * The profile the tenant was set up with.
     *
     * @throws UnknownFiscalProfileException when the fixed profile is not available
     */
    public function current(): FiscalProfile;

    public function find(string $code): ?FiscalProfile;

    /**
     * @return list<FiscalProfile>
     */
    public function all(): array;
}
