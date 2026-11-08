<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;

/**
 * Data for creating a service group (German UI: "Leistungsgruppe"). The group
 * is appended to the contact's groups; `memberIds` are subscriptions of the
 * same contact, in the order they get inside the group (members of another
 * group move over). `name` is 1 to 120 characters, the core validates it.
 */
final readonly class NewSubscriptionGroup
{
    /**
     * @param  list<int>  $memberIds
     */
    public function __construct(
        public int $contactId,
        public string $name,
        public bool $collapseOnInvoice = true,
        public ?string $note = null,
        public array $memberIds = [],
    ) {
        if (! array_is_list($memberIds)) {
            throw new ContractException('memberIds must be a list.');
        }
    }
}
