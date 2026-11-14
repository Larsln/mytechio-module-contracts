<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Organization;

/**
 * The company's own master data from the core's organization settings
 * (since 2.19), read-only.
 */
interface OrganizationProfile
{
    /** The company's data; null while the organization is not set up. */
    public function current(): ?OrganizationData;
}
