<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;

/**
 * Read-only view of a service group (German UI: "Leistungsgruppe") in the
 * core: a named group of one contact's subscriptions that the billing run
 * bills together on one invoice item group.
 *
 * `memberIds` are the ids of the member subscriptions in group order (the
 * member's `SubscriptionData::$groupPosition` is its index + 1); `position`
 * is the 1-based, dense position of the group among the contact's groups.
 * `collapseOnInvoice` tells whether the invoice shows the group as one line
 * with its sum. `invoiceGroupId` is the UUID the billing run uses as `group_id`
 * of the invoice items: the name-based UUIDv5 (URL namespace) of
 * `mytechio:subscription-group:<id>`, see {@see invoiceGroupIdFor()}.
 * `createdAt` is ISO 8601.
 */
final readonly class SubscriptionGroupData
{
    /**
     * @param  list<int>  $memberIds
     */
    public function __construct(
        public int $id,
        public int $contactId,
        public string $name,
        public int $position,
        public bool $collapseOnInvoice,
        public ?string $note,
        public array $memberIds,
        public string $invoiceGroupId,
        public string $createdAt,
    ) {
        if ($name === '') {
            throw new ContractException('name must not be empty.');
        }

        if ($position < 1) {
            throw new ContractException('position must be at least 1.');
        }

        if (! array_is_list($memberIds)) {
            throw new ContractException('memberIds must be a list.');
        }
    }

    /**
     * The invoice group id of a group: UUIDv5 in the URL namespace of
     * `mytechio:subscription-group:<groupId>`, computed without a UUID
     * library; it equals the core's `SubscriptionGroup::invoiceGroupIdFor()`.
     */
    public static function invoiceGroupIdFor(int $groupId): string
    {
        $hash = sha1(hex2bin('6ba7b8119dad11d180b400c04fd430c8').'mytechio:subscription-group:'.$groupId);
        $time = (hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x5000;
        $clock = (hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000;

        return sprintf('%s-%s-%04x-%04x-%s', substr($hash, 0, 8), substr($hash, 8, 4), $time, $clock, substr($hash, 20, 12));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'name' => $this->name,
            'position' => $this->position,
            'collapse_on_invoice' => $this->collapseOnInvoice,
            'note' => $this->note,
            'member_ids' => $this->memberIds,
            'invoice_group_id' => $this->invoiceGroupId,
            'created_at' => $this->createdAt,
        ];
    }
}
