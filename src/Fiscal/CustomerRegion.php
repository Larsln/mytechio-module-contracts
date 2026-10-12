<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Where a customer is established as the place-of-supply rules see it,
 * relative to the profile's country.
 */
enum CustomerRegion: string
{
    case Domestic = 'domestic';
    case EuMember = 'eu_member';
    case ThirdCountry = 'third_country';
}
