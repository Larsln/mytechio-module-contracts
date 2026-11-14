<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Organization\OrganizationData;
use MyTechIO\Contracts\Organization\OrganizationProfile;

/** Test double for `OrganizationProfile`: empty until `seed()` declares the company. */
final class FakeOrganizationProfile implements OrganizationProfile
{
    private ?OrganizationData $organization = null;

    public function seed(?OrganizationData $organization): void
    {
        $this->organization = $organization;
    }

    public function current(): ?OrganizationData
    {
        return $this->organization;
    }
}
