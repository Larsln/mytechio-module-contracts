<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * Kind of a type-specific subscription field.
 *
 * `Secret` is free text that the core stores encrypted and masks in the UI.
 */
enum SubscriptionFieldKind: string
{
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Bool = 'bool';
    case Select = 'select';
    case Secret = 'secret';
}
