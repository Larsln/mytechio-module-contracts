<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeOrganizationProfile;
use MyTechIO\Contracts\Organization\OrganizationData;
use MyTechIO\Contracts\Organization\OrganizationProfile;

it('has no company until one is declared and then hands it out', function () {
    $profile = new FakeOrganizationProfile;

    expect($profile)->toBeInstanceOf(OrganizationProfile::class)->and($profile->current())->toBeNull();

    $profile->seed(new OrganizationData('MyTechIO AG', 'Musterstraße 1', '10115', 'Berlin', 'DE', email: 'info@example.test', vatId: 'DE123456789'));

    expect($profile->current()?->toArray())->toBe([
        'legal_name' => 'MyTechIO AG', 'street' => 'Musterstraße 1', 'address_addition' => null, 'postal_code' => '10115', 'city' => 'Berlin',
        'country_code' => 'DE', 'email' => 'info@example.test', 'phone' => null, 'website' => null, 'vat_id' => 'DE123456789',
    ]);

    $profile->seed(null);
    expect($profile->current())->toBeNull();
});
