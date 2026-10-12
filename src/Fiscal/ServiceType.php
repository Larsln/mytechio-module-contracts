<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Kind of an other service as far as its place of supply depends on it:
 * the general rule, a catalogue service (consulting, IT, data processing) or
 * work performed at the customer's location.
 */
enum ServiceType: string
{
    case General = 'general';
    case Catalogue = 'catalogue';
    case OnSite = 'on_site';
}
